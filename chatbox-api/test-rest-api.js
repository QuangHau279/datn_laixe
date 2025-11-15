import dotenv from 'dotenv';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const envPath = join(__dirname, '.env');

dotenv.config({ path: envPath });

const API_KEY = process.env.GEMINI_API_KEY;

if (!API_KEY) {
  console.error('❌ GEMINI_API_KEY không tìm thấy');
  process.exit(1);
}

console.log('='.repeat(60));
console.log('🔍 TEST GEMINI API VỚI REST API TRỰC TIẾP');
console.log('='.repeat(60));
console.log(`API Key: ${API_KEY.substring(0, 20)}...`);
console.log('');

// Thử các endpoint và model name khác nhau
const testCases = [
  {
    name: 'gemini-pro (REST)',
    url: `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${API_KEY}`,
    method: 'POST'
  },
  {
    name: 'gemini-1.5-flash (REST)',
    url: `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${API_KEY}`,
    method: 'POST'
  },
  {
    name: 'gemini-1.5-pro (REST)',
    url: `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=${API_KEY}`,
    method: 'POST'
  },
  {
    name: 'List models endpoint',
    url: `https://generativelanguage.googleapis.com/v1beta/models?key=${API_KEY}`,
    method: 'GET'
  }
];

for (const testCase of testCases) {
  try {
    console.log(`📝 Testing: ${testCase.name}...`);
    
    const options = {
      method: testCase.method,
      headers: {
        'Content-Type': 'application/json'
      }
    };
    
    if (testCase.method === 'POST') {
      options.body = JSON.stringify({
        contents: [{
          parts: [{
            text: 'Hello'
          }]
        }]
      });
    }
    
    const response = await fetch(testCase.url, options);
    const status = response.status;
    const data = await response.json();
    
    if (status === 200) {
      if (testCase.name.includes('List models')) {
        console.log(`   ✅ THÀNH CÔNG! Tìm thấy ${data.models?.length || 0} models`);
        if (data.models && data.models.length > 0) {
          console.log(`   📋 Các model khả dụng:`);
          data.models.slice(0, 10).forEach(model => {
            console.log(`      - ${model.name}`);
          });
        }
      } else {
        console.log(`   ✅ THÀNH CÔNG! Model hoạt động`);
        if (data.candidates && data.candidates[0]?.content?.parts) {
          const text = data.candidates[0].content.parts[0].text;
          console.log(`   📄 Response: ${text.substring(0, 100)}...`);
        }
      }
    } else {
      console.log(`   ❌ Status: ${status}`);
      if (data.error) {
        console.log(`   📝 Error: ${JSON.stringify(data.error)}`);
      }
    }
  } catch (error) {
    console.log(`   ❌ Lỗi: ${error.message}`);
  }
  
  await new Promise(resolve => setTimeout(resolve, 1000));
}

console.log('\n' + '='.repeat(60));

