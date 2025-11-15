import dotenv from 'dotenv';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import { GoogleGenerativeAI } from '@google/generative-ai';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const envPath = join(__dirname, '.env');

dotenv.config({ path: envPath });

const API_KEY = process.env.GEMINI_API_KEY;

if (!API_KEY) {
  console.error('❌ GEMINI_API_KEY không tìm thấy trong file .env');
  process.exit(1);
}

console.log('='.repeat(60));
console.log('🔍 TEST GEMINI API KEY');
console.log('='.repeat(60));
console.log(`API Key: ${API_KEY.substring(0, 20)}...`);
console.log(`API Key length: ${API_KEY.length} characters`);
console.log('');

// Kiểm tra format API key
if (!API_KEY.startsWith('AIza')) {
  console.warn('⚠️  CẢNH BÁO: API key không bắt đầu bằng "AIza"');
  console.warn('   API key hợp lệ thường bắt đầu bằng "AIzaSy..."');
}

const genAI = new GoogleGenerativeAI(API_KEY);

// Danh sách model để test - sử dụng các model mới nhất từ Google
const modelsToTest = [
  'gemini-2.5-flash',          // Model mới nhất, nhanh, ổn định
  'models/gemini-2.5-flash',   // Với prefix
  'gemini-2.5-pro',            // Model mạnh hơn
  'models/gemini-2.5-pro',     // Với prefix
  'gemini-2.0-flash',          // Model 2.0 stable
  'models/gemini-2.0-flash',   // Với prefix
  'gemini-2.0-flash-exp',      // Model experimental
  'models/gemini-2.0-flash-exp', // Với prefix
];

console.log('🔄 Đang test các model...\n');

let foundModel = null;

for (const modelName of modelsToTest) {
  try {
    console.log(`📝 Testing: ${modelName}...`);
    const model = genAI.getGenerativeModel({ model: modelName });
    
    // Test với prompt đơn giản
    const result = await model.generateContent('Say hello');
    const response = result.response;
    const text = response.text();
    
    if (text && text.length > 0) {
      console.log(`   ✅ THÀNH CÔNG! Model "${modelName}" hoạt động`);
      console.log(`   📄 Response: ${text.substring(0, 100)}...`);
      foundModel = modelName;
      break;
    }
  } catch (error) {
    const errorMsg = error.message || error.toString();
    const errorCode = error.code || '';
    
    if (errorMsg.includes('404') || errorCode === '404') {
      console.log(`   ❌ Model không tồn tại (404)`);
    } else if (errorMsg.includes('403') || errorCode === '403') {
      console.log(`   ❌ Không có quyền truy cập (403)`);
      console.log(`   💡 Có thể cần enable Gemini API trong Google Cloud Console`);
    } else if (errorMsg.includes('401') || errorCode === '401') {
      console.log(`   ❌ API key không hợp lệ (401)`);
    } else if (errorMsg.includes('429') || errorCode === '429') {
      console.log(`   ⚠️  Quá nhiều requests (429) - Model có thể khả dụng nhưng bị rate limit`);
    } else {
      console.log(`   ❌ Lỗi: ${errorMsg.substring(0, 100)}`);
      if (errorCode) {
        console.log(`   📝 Error code: ${errorCode}`);
      }
    }
  }
  
  // Delay nhỏ giữa các request để tránh rate limit
  await new Promise(resolve => setTimeout(resolve, 500));
}

console.log('\n' + '='.repeat(60));
if (foundModel) {
  console.log(`✅ KẾT QUẢ: Tìm thấy model khả dụng: ${foundModel}`);
  console.log(`💡 Sử dụng model này trong server.js`);
} else {
  console.log('❌ KẾT QUẢ: Không tìm thấy model nào khả dụng');
  console.log('\n💡 Các bước khắc phục:');
  console.log('   1. Kiểm tra API key tại: https://aistudio.google.com/app/apikey');
  console.log('   2. Đảm bảo API key bắt đầu bằng "AIzaSy..."');
  console.log('   3. Kiểm tra API key có quyền truy cập Gemini API');
  console.log('   4. Enable Gemini API trong Google Cloud Console nếu cần');
  console.log('   5. Kiểm tra kết nối mạng');
}
console.log('='.repeat(60));

