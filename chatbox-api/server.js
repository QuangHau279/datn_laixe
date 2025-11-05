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

// Log để debug
console.log('🔍 Debug Environment:');
console.log('   __dirname:', __dirname);
console.log('   .env path:', envPath);
console.log('   .env exists:', existsSync(envPath));
console.log('   GEMINI_API_KEY:', process.env.GEMINI_API_KEY ? `✓ (${process.env.GEMINI_API_KEY.substring(0, 10)}...)` : '✗ CHƯA CÓ');
console.log('   PORT:', process.env.PORT || 7070);

const app = express();

// CORS configuration - cho phép gọi từ trình duyệt
app.use(cors({
  origin: '*', // Cho phép tất cả origins (production nên giới hạn)
  methods: ['GET', 'POST', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
  credentials: false
}));

app.use(express.json({ limit: '1mb' }));

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

// Thử các model name khác nhau - có thể cần prefix "models/"
// Nếu model đầu không work, code sẽ tự động thử model tiếp theo
const MODEL_NAMES = [
  'gemini-1.5-flash',           // Model phổ biến nhất, nhanh
  'gemini-1.5-pro',            // Model mạnh hơn
  'gemini-2.0-flash-exp',      // Model mới nhất (experimental)
  'gemini-pro',                // Model cũ nhưng ổn định
  'models/gemini-1.5-flash',   // Với prefix models/
  'models/gemini-1.5-pro',     // Với prefix models/
  'models/gemini-pro',         // Với prefix models/
  'gemini-1.0-pro',            // Version cũ
  'models/gemini-1.0-pro'      // Version cũ với prefix
];

// Initialize model - thử từng model cho đến khi tìm được model hoạt động
let model = null;
let modelName = null;

async function initializeModel() {
  console.log('🔄 Đang thử các model với API key mới...');
  console.log(`🔑 API Key: ${process.env.GEMINI_API_KEY.substring(0, 20)}...`);
  
  // Thử từng model trong danh sách
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
        return; // Tìm thấy model hoạt động, dừng lại
      }
    } catch (err) {
      // Model này không hoạt động, thử model tiếp theo
      const errorMsg = err.message || err.toString();
      if (errorMsg.includes('404') || errorMsg.includes('not found')) {
        console.log(`   ❌ Model "${name}" không tồn tại`);
      } else if (errorMsg.includes('403') || errorMsg.includes('API key')) {
        console.log(`   ❌ Model "${name}" - Lỗi API key hoặc quyền truy cập`);
        console.log(`   💡 Hãy kiểm tra API key có hợp lệ và có quyền truy cập model không`);
      } else {
        console.log(`   ❌ Model "${name}" không khả dụng: ${errorMsg.substring(0, 80)}`);
      }
      continue;
    }
  }
  
  // Nếu không tìm thấy model nào hoạt động, dùng model đầu tiên và để runtime error
  if (!model) {
    console.warn('⚠️  Không tìm thấy model nào khả dụng sau khi test');
    console.warn('📝 Sẽ thử model đầu tiên khi có request');
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
    console.log('[Chat API] Processing message:', msg.substring(0, 100));

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
    const result = await model.generateContent(prompt);
    const answer = result.response.text();
    console.log('[Chat API] Response generated, length:', answer.length);
    res.json({ answer });
  } catch (e) {
    console.error('[Chat API] Error:', e.message);
    console.error('[Chat API] Error stack:', e.stack);
    
    // Thử reinitialize model nếu lỗi
    if (e.message.includes('404') || e.message.includes('not found')) {
      console.log('[Chat API] Model không khả dụng, thử reinitialize...');
      await initializeModel();
    }
    
    res.status(500).json({ 
      error: e.message || 'Lỗi máy chủ',
      details: 'Có thể model không khả dụng. Vui lòng kiểm tra API key.'
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
    note: 'API không hỗ trợ listModels(). Code sẽ tự động thử các model trong danh sách.'
  });
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
  console.log(`📝 GEMINI_API_KEY: ${process.env.GEMINI_API_KEY ? '✓ Đã cấu hình' : '✗ CHƯA CÓ'}`);
  console.log('='.repeat(50));
  
  // Initialize model khi server start
  await initializeModel();
});
