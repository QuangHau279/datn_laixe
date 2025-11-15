import dotenv from 'dotenv';
import express from 'express';
import cors from 'cors';
import { GoogleGenerativeAI } from '@google/generative-ai';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import { existsSync } from 'fs';

// Lấy đường dẫn thư mục hiện tại (ES modules)
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Đường dẫn file .env trong thư mục chatbox-api
const envPath = join(__dirname, '.env');

// Load file .env từ thư mục hiện tại
const envResult = dotenv.config({ path: envPath });

// Kiểm tra file .env có tồn tại không
if (!existsSync(envPath)) {
  console.warn('⚠️  Cảnh báo: File .env không tìm thấy tại:', envPath);
  console.warn('📝 Vui lòng tạo file .env trong thư mục chatbox-api/');
} else if (envResult.error) {
  console.warn('⚠️  Cảnh báo: Có lỗi khi đọc file .env:', envResult.error);
} else {
  console.log('✅ Đã load file .env thành công từ:', envPath);
  // Debug: kiểm tra dotenv đã parse được bao nhiêu biến
  if (envResult.parsed) {
    console.log('📦 Số biến được parse:', Object.keys(envResult.parsed).length);
    console.log('📦 Các biến được parse:', Object.keys(envResult.parsed));
  } else {
    console.warn('⚠️  Không có biến nào được parse từ file .env');
    console.warn('⚠️  Có thể file .env trống hoặc format sai');
  }
}

// Cấu hình Session Limit
const SESSION_LIMIT = parseInt(process.env.SESSION_LIMIT || '10', 10); // Mặc định 10 câu/session
const SESSION_TIMEOUT = parseInt(process.env.SESSION_TIMEOUT || '3600', 10) * 1000; // Mặc định 1 giờ (ms)
const ENABLE_IP_LIMIT = process.env.ENABLE_IP_LIMIT === 'true'; // Giới hạn theo IP

// Log để debug
console.log('🔍 Debug Environment:');
console.log('   __dirname:', __dirname);
console.log('   .env path:', envPath);
console.log('   .env exists:', existsSync(envPath));
console.log('   GEMINI_API_KEY:', process.env.GEMINI_API_KEY ? `✓ (${process.env.GEMINI_API_KEY.substring(0, 10)}...)` : '✗ CHƯA CÓ');
console.log('   PORT:', process.env.PORT || 7070);
console.log('   SESSION_LIMIT:', SESSION_LIMIT, 'câu/session');
console.log('   SESSION_TIMEOUT:', SESSION_TIMEOUT / 1000, 'giây');
console.log('   ENABLE_IP_LIMIT:', ENABLE_IP_LIMIT);

const app = express();

// CORS configuration - cho phép gọi từ trình duyệt
app.use(cors({
  origin: '*', // Cho phép tất cả origins (production nên giới hạn)
  methods: ['GET', 'POST', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
  credentials: true // Cho phép cookie
}));

app.use(express.json({ limit: '1mb' }));

// ===== SESSION MANAGEMENT =====
// In-memory session storage: { sessionId: { count: number, expiresAt: timestamp, ip: string } }
const sessions = new Map();

// Helper: Tạo session ID mới
function generateSessionId() {
  return 'sess_' + Date.now() + '_' + Math.random().toString(36).substring(2, 15);
}

// Helper: Lấy session ID từ cookie hoặc tạo mới
function getSessionId(req) {
  // Parse cookie header
  const cookieHeader = req.headers.cookie || '';
  const cookies = {};
  cookieHeader.split(';').forEach(cookie => {
    const [key, value] = cookie.trim().split('=');
    if (key && value) {
      cookies[key] = decodeURIComponent(value);
    }
  });
  
  return cookies['chat_session_id'] || null;
}

// Helper: Set cookie
function setSessionCookie(res, sessionId) {
  const maxAge = SESSION_TIMEOUT / 1000; // Convert to seconds
  res.setHeader('Set-Cookie', `chat_session_id=${sessionId}; Path=/; Max-Age=${maxAge}; HttpOnly; SameSite=Lax`);
}

// Helper: Lấy IP address
function getClientIP(req) {
  return req.headers['x-forwarded-for']?.split(',')[0] || 
         req.headers['x-real-ip'] || 
         req.connection?.remoteAddress || 
         req.socket?.remoteAddress || 
         'unknown';
}

// Helper: Kiểm tra và tăng count cho session
function checkAndIncrementSession(req) {
  const sessionId = getSessionId(req) || generateSessionId();
  const ip = getClientIP(req);
  const now = Date.now();
  
  let session = sessions.get(sessionId);
  
  // Nếu session không tồn tại hoặc đã hết hạn, tạo mới
  if (!session || session.expiresAt < now) {
    session = {
      count: 0,
      expiresAt: now + SESSION_TIMEOUT,
      ip: ip,
      createdAt: now
    };
    sessions.set(sessionId, session);
  }
  
  // Kiểm tra IP limit nếu bật
  if (ENABLE_IP_LIMIT && session.ip !== ip) {
    return {
      allowed: false,
      sessionId: sessionId,
      reason: 'IP address không khớp với session',
      remaining: 0,
      limit: SESSION_LIMIT
    };
  }
  
  // Kiểm tra limit
  if (session.count >= SESSION_LIMIT) {
    return {
      allowed: false,
      sessionId: sessionId,
      reason: 'Đã đạt giới hạn số câu hỏi trong session này',
      remaining: 0,
      limit: SESSION_LIMIT,
      resetAt: new Date(session.expiresAt).toISOString()
    };
  }
  
  // Tăng count
  session.count++;
  sessions.set(sessionId, session);
  
  return {
    allowed: true,
    sessionId: sessionId,
    remaining: SESSION_LIMIT - session.count,
    limit: SESSION_LIMIT,
    resetAt: new Date(session.expiresAt).toISOString()
  };
}

// Cleanup job: Xóa các session đã hết hạn (chạy mỗi 5 phút)
setInterval(() => {
  const now = Date.now();
  let cleaned = 0;
  
  for (const [sessionId, session] of sessions.entries()) {
    if (session.expiresAt < now) {
      sessions.delete(sessionId);
      cleaned++;
    }
  }
  
  if (cleaned > 0) {
    console.log(`[Session Cleanup] Đã xóa ${cleaned} session hết hạn. Còn lại: ${sessions.size} sessions`);
  }
}, 5 * 60 * 1000); // 5 phút

console.log('✅ Session management đã được khởi tạo');
console.log(`   - Giới hạn: ${SESSION_LIMIT} câu/session`);
console.log(`   - Timeout: ${SESSION_TIMEOUT / 1000} giây`);
console.log(`   - IP Limit: ${ENABLE_IP_LIMIT ? 'Bật' : 'Tắt'}`);

// Logging middleware
app.use((req, res, next) => {
  console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`);
  next();
});

// Kiểm tra GEMINI_API_KEY sau khi đã load dotenv
if (!process.env.GEMINI_API_KEY) {
  console.error('❌ LỖI: GEMINI_API_KEY chưa được cấu hình!');
  console.error('📝 Vui lòng tạo file .env trong thư mục chatbox-api/ với nội dung:');
  console.error('   GEMINI_API_KEY=your_api_key_here');
  console.error('   PORT=7070');
  console.error('');
  console.error('📁 Đường dẫn file .env mong đợi:', envPath);
  process.exit(1);
}

const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);

// Thử các model name khác nhau - ưu tiên model cơ bản nhất trước
// Cập nhật với các model mới nhất từ Google Gemini API
const MODEL_NAMES = [
  // Model mới nhất và ổn định nhất (2024-2025)
  'gemini-2.5-flash',          // Model nhanh, miễn phí, ổn định
  'models/gemini-2.5-flash',   // Với prefix models/
  'gemini-2.5-pro',            // Model mạnh hơn
  'models/gemini-2.5-pro',     // Với prefix models/
  
  // Model preview (thử nếu model stable không hoạt động)
  'gemini-2.5-flash-preview-05-20',
  'models/gemini-2.5-flash-preview-05-20',
  'gemini-2.5-pro-preview-06-05',
  'models/gemini-2.5-pro-preview-06-05',
  
  // Model 2.0 (fallback)
  'gemini-2.0-flash',          // Model 2.0 stable
  'models/gemini-2.0-flash',   // Với prefix models/
  'gemini-2.0-flash-exp',      // Model experimental
  'models/gemini-2.0-flash-exp' // Với prefix models/
];

// Initialize model - thử từng model cho đến khi tìm được model hoạt động
let model = null;
let modelName = null;

async function initializeModel() {
  console.log('🔄 Đang thử các model với API key mới...');
  console.log(`🔑 API Key: ${process.env.GEMINI_API_KEY.substring(0, 20)}...`);
  
  // Thử list models trước để xem model nào có sẵn
  try {
    console.log('   📋 Đang thử lấy danh sách models có sẵn...');
    // Note: @google/generative-ai không có listModels API trực tiếp
    // Nên chúng ta sẽ thử từng model
  } catch (err) {
    console.log('   ⚠️  Không thể lấy danh sách models:', err.message);
  }
  
  // Thử từng model trong danh sách (từ cơ bản nhất đến mới nhất)
  for (const name of MODEL_NAMES) {
    try {
      console.log(`   Đang thử: ${name}...`);
      const testModel = genAI.getGenerativeModel({ model: name });
      // Test với prompt rất ngắn để xem model có hoạt động không
      const testPrompt = 'Hi';
      const testResult = await testModel.generateContent(testPrompt);
      
      if (testResult && testResult.response && testResult.response.text) {
        model = testModel;
        modelName = name;
        console.log(`   ✅ Model hoạt động: ${name}`);
        console.log(`   📝 Sẽ sử dụng model này cho tất cả requests`);
        return; // Tìm thấy model hoạt động, dừng lại
      }
    } catch (err) {
      // Model này không hoạt động, thử model tiếp theo
      const errorMsg = err.message || err.toString();
      const errorCode = err.code || '';
      const fullError = JSON.stringify(err, Object.getOwnPropertyNames(err));
      
      if (errorMsg.includes('404') || errorMsg.includes('not found') || errorCode === '404') {
        console.log(`   ❌ Model "${name}" không tồn tại (404)`);
      } else if (errorMsg.includes('403') || errorMsg.includes('API key') || errorMsg.includes('permission') || errorCode === '403') {
        console.log(`   ❌ Model "${name}" - Lỗi API key hoặc quyền truy cập (403)`);
        console.log(`   💡 Hãy kiểm tra API key có hợp lệ và có quyền truy cập model không`);
        console.log(`   💡 Có thể API key chưa được kích hoạt hoặc hết hạn`);
      } else if (errorMsg.includes('429') || errorCode === '429') {
        console.log(`   ⚠️  Model "${name}" - Quá nhiều requests (429), thử lại sau`);
      } else if (errorMsg.includes('401') || errorCode === '401') {
        console.log(`   ❌ Model "${name}" - API key không hợp lệ (401)`);
        console.log(`   💡 Vui lòng kiểm tra lại GEMINI_API_KEY trong file .env`);
      } else {
        console.log(`   ❌ Model "${name}" không khả dụng`);
        console.log(`   📝 Chi tiết lỗi: ${errorMsg.substring(0, 150)}`);
        if (errorCode) {
          console.log(`   📝 Error code: ${errorCode}`);
        }
      }
      continue;
    }
  }
  
  // Nếu không tìm thấy model nào hoạt động
  if (!model) {
    console.error('='.repeat(50));
    console.error('❌ KHÔNG TÌM THẤY MODEL NÀO KHẢ DỤNG');
    console.error('='.repeat(50));
    console.error('📝 Đã thử tất cả các model sau:');
    MODEL_NAMES.forEach((name, idx) => {
      console.error(`   ${idx + 1}. ${name}`);
    });
    console.error('');
    console.error('💡 Các nguyên nhân có thể:');
    console.error('   1. API key không hợp lệ hoặc đã hết hạn');
    console.error('   2. API key chưa được kích hoạt trong Google AI Studio');
    console.error('   3. API key không có quyền truy cập các model Gemini');
    console.error('   4. Vấn đề về mạng hoặc firewall');
    console.error('   5. Google API đang bảo trì hoặc có sự cố');
    console.error('');
    console.error('🔧 Cách khắc phục:');
    console.error('   1. Kiểm tra API key tại: https://makersuite.google.com/app/apikey');
    console.error('   2. Tạo API key mới nếu cần');
    console.error('   3. Đảm bảo API key có quyền truy cập Gemini API');
    console.error('   4. Kiểm tra kết nối mạng');
    console.error('='.repeat(50));
    
    // Vẫn tạo model với model đầu tiên để có thể thử lại khi có request
    console.warn('⚠️  Sẽ thử model cơ bản nhất (gemini-pro) khi có request');
    modelName = MODEL_NAMES[0];
    model = genAI.getGenerativeModel({ model: modelName });
  }
}

// API: nhận {message} -> trả {answer}
app.post('/chat', async (req, res) => {
  try {
    console.log('[Chat API] Received request:', req.body);
    const msg = (req.body?.message || '').toString().slice(0, 2000);
    if (!msg) {
      console.log('[Chat API] Missing message');
      return res.status(400).json({ error: 'Thiếu message' });
    }
    
    // Kiểm tra session limit
    const sessionCheck = checkAndIncrementSession(req);
    
    // Set cookie nếu chưa có
    if (sessionCheck.sessionId) {
      setSessionCookie(res, sessionCheck.sessionId);
    }
    
    // Nếu vượt quá limit, trả về lỗi
    if (!sessionCheck.allowed) {
      console.log(`[Chat API] Session limit exceeded: ${sessionCheck.reason}`);
      return res.status(429).json({
        error: 'Đã đạt giới hạn số câu hỏi',
        message: sessionCheck.reason,
        remaining: sessionCheck.remaining,
        limit: sessionCheck.limit,
        resetAt: sessionCheck.resetAt,
        suggestion: `Bạn đã sử dụng hết ${sessionCheck.limit} câu hỏi trong session này. Vui lòng quay lại sau ${Math.ceil((new Date(sessionCheck.resetAt) - Date.now()) / 1000 / 60)} phút.`
      });
    }
    
    console.log(`[Chat API] Processing message (${sessionCheck.remaining}/${sessionCheck.limit} còn lại):`, msg.substring(0, 100));

    // Đảm bảo model đã được initialize
    if (!model) {
      await initializeModel();
    }
    
    // Nếu vẫn không có model, trả lỗi
    if (!model) {
      return res.status(500).json({ 
        error: 'Không tìm thấy model nào khả dụng',
        details: 'Tất cả các model đã thử đều không hoạt động. Vui lòng kiểm tra API key.'
      });
    }

    const system = `Bạn là trợ lý AI chuyên về lý thuyết lái xe và luật giao thông Việt Nam. Nhiệm vụ của bạn:

1. TRẢ LỜI VỀ LÝ THUYẾT LÁI XE (600 câu):
   - Giải thích các khái niệm, quy tắc giao thông
   - Phân tích câu hỏi thi bằng lái (A1, A2, B1, B2, C, D, E, F)
   - Giải thích biển báo giao thông, vạch kẻ đường, tín hiệu đèn giao thông
   - Hướng dẫn xử lý tình huống trong bài thi mô phỏng
   - Nhắc về độ tuổi lái xe, thời hạn bằng lái, xử phạt vi phạm

2. NGUYÊN TẮC TRẢ LỜI:
   - Ngắn gọn, rõ ràng, dễ hiểu (200-300 từ)
   - Chính xác theo luật giao thông Việt Nam hiện hành
   - Ưu tiên bảo đảm an toàn giao thông
   - Dùng ngôn ngữ thân thiện, khuyến khích
   - Nếu không chắc chắn, nói thật và hướng dẫn tham khảo tài liệu chính thức

3. KHÔNG TRẢ LỜI:
   - Câu hỏi không liên quan đến giao thông/lái xe
   - Hỏi về lịch sử, giải trí, thể thao, tin tức
   - Yêu cầu làm bài thi hộ hoặc gian lận

Hãy trả lời câu hỏi của người dùng theo các nguyên tắc trên:`;

    const prompt = `${system}\n\nCâu hỏi của người dùng: ${msg}`;

    console.log(`[Chat API] Using model: ${modelName || 'unknown'}`);
    
    // Gọi Gemini API với format đúng - chỉ cần truyền prompt string
    let result;
    try {
      result = await model.generateContent(prompt);
      const answer = result.response.text();
      console.log('[Chat API] Response generated, length:', answer.length);
      
      // Trả về response với thông tin session
      res.json({ 
        answer,
        remaining: sessionCheck.remaining,
        limit: sessionCheck.limit,
        resetAt: sessionCheck.resetAt
      });
    } catch (modelError) {
      // Nếu model hiện tại lỗi, thử reinitialize với model khác
      console.error('[Chat API] Model error:', modelError.message);
      console.log('[Chat API] Thử reinitialize với model khác...');
      
      // Reset model để thử lại
      model = null;
      modelName = null;
      await initializeModel();
      
      // Nếu tìm được model mới, thử lại request
      if (model) {
        console.log(`[Chat API] Retry với model mới: ${modelName}`);
        result = await model.generateContent(prompt);
        const answer = result.response.text();
        console.log('[Chat API] Response generated sau retry, length:', answer.length);
        
        // Trả về response với thông tin session
        res.json({ 
          answer,
          remaining: sessionCheck.remaining,
          limit: sessionCheck.limit,
          resetAt: sessionCheck.resetAt
        });
      } else {
        throw new Error('Không tìm thấy model nào khả dụng sau khi retry');
      }
    }
  } catch (e) {
    console.error('[Chat API] Error:', e.message);
    console.error('[Chat API] Error stack:', e.stack);
    
    // Phân tích lỗi chi tiết
    const errorMsg = e.message || '';
    let errorDetails = 'Có thể model không khả dụng. Vui lòng kiểm tra API key.';
    
    if (errorMsg.includes('404') || errorMsg.includes('not found')) {
      errorDetails = 'Model không tồn tại. Vui lòng kiểm tra API key có quyền truy cập Gemini API.';
    } else if (errorMsg.includes('403') || errorMsg.includes('permission')) {
      errorDetails = 'API key không có quyền truy cập. Vui lòng kiểm tra quyền của API key.';
    } else if (errorMsg.includes('401')) {
      errorDetails = 'API key không hợp lệ. Vui lòng kiểm tra lại GEMINI_API_KEY trong file .env.';
    } else if (errorMsg.includes('429')) {
      errorDetails = 'Quá nhiều requests. Vui lòng thử lại sau.';
    }
    
    res.status(500).json({ 
      error: e.message || 'Lỗi máy chủ',
      details: errorDetails,
      suggestion: 'Kiểm tra API key tại: https://makersuite.google.com/app/apikey'
    });
  }
});

app.get('/', (req, res) => {
  console.log('[Chat API] Health check request');
  res.json({ 
    status: 'ok', 
    service: 'Chatbox API',
    port: process.env.PORT || 7070,
    timestamp: new Date().toISOString()
  });
});

// Endpoint để xem model đang dùng và danh sách models đã thử
app.get('/models', async (req, res) => {
  res.json({ 
    currentModel: modelName || 'Chưa được chọn',
    testedModels: MODEL_NAMES,
    status: model ? 'Đã khởi tạo' : 'Chưa khởi tạo',
    apiKeyConfigured: !!process.env.GEMINI_API_KEY,
    apiKeyPrefix: process.env.GEMINI_API_KEY ? process.env.GEMINI_API_KEY.substring(0, 10) + '...' : 'N/A',
    note: 'API không hỗ trợ listModels(). Code sẽ tự động thử các model trong danh sách từ cơ bản nhất đến mới nhất.'
  });
});

// Endpoint để xem thông tin session hiện tại
app.get('/session', (req, res) => {
  const sessionId = getSessionId(req);
  const ip = getClientIP(req);
  
  if (!sessionId) {
    return res.json({
      hasSession: false,
      message: 'Chưa có session. Session sẽ được tạo khi gửi câu hỏi đầu tiên.',
      limit: SESSION_LIMIT,
      timeout: SESSION_TIMEOUT / 1000
    });
  }
  
  const session = sessions.get(sessionId);
  
  if (!session || session.expiresAt < Date.now()) {
    return res.json({
      hasSession: false,
      message: 'Session đã hết hạn',
      limit: SESSION_LIMIT,
      timeout: SESSION_TIMEOUT / 1000
    });
  }
  
  res.json({
    hasSession: true,
    remaining: SESSION_LIMIT - session.count,
    used: session.count,
    limit: SESSION_LIMIT,
    resetAt: new Date(session.expiresAt).toISOString(),
    createdAt: new Date(session.createdAt).toISOString(),
    ip: ENABLE_IP_LIMIT ? session.ip : undefined
  });
});

// Endpoint để test API key và thử lại initialize model
app.post('/test-models', async (req, res) => {
  try {
    console.log('[Test Models] Đang test lại tất cả models...');
    model = null;
    modelName = null;
    await initializeModel();
    
    res.json({
      success: !!model,
      currentModel: modelName || 'Không tìm thấy',
      message: model ? `Đã tìm thấy model: ${modelName}` : 'Không tìm thấy model nào khả dụng',
      testedModels: MODEL_NAMES
    });
  } catch (e) {
    res.status(500).json({
      success: false,
      error: e.message,
      message: 'Lỗi khi test models'
    });
  }
});

const PORT = process.env.PORT || 7070;
app.listen(PORT, async () => {
  console.log('='.repeat(50));
  console.log('🤖 Chatbox API Server');
  console.log('='.repeat(50));
  console.log(`✅ Server đang chạy tại: http://localhost:${PORT}`);
  console.log(`✅ Health check: http://localhost:${PORT}/`);
  console.log(`✅ Chat endpoint: http://localhost:${PORT}/chat`);
  console.log(`✅ List models: http://localhost:${PORT}/models`);
  console.log(`✅ Test models: http://localhost:${PORT}/test-models (POST)`);
  console.log(`✅ Session info: http://localhost:${PORT}/session`);
  console.log(`📝 GEMINI_API_KEY: ${process.env.GEMINI_API_KEY ? '✓ Đã cấu hình' : '✗ CHƯA CÓ'}`);
  console.log(`🔒 Session Limit: ${SESSION_LIMIT} câu/session, ${SESSION_TIMEOUT / 1000}s timeout`);
  console.log('='.repeat(50));
  
  // Initialize model khi server start
  await initializeModel();
});
