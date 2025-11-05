# 🤖 Chatbox AI - Trợ lý Lý thuyết Lái xe

Chatbox AI hỗ trợ người dùng học lý thuyết lái xe với Google Gemini AI.

## 🚀 Khởi động nhanh

### 1. Cài đặt dependencies

```bash
npm install
```

### 2. Tạo file .env

```bash
# Tạo file .env
echo "GEMINI_API_KEY=your_gemini_api_key_here" > .env
echo "PORT=7070" >> .env
```

**Lấy Gemini API Key:**

1. Truy cập [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Đăng nhập bằng Google account
3. Tạo API key mới
4. Copy key vào `.env`

### 3. Khởi động server

```bash
# Development mode (auto-reload)
npm run dev

# Production mode
npm start
```

Server chạy tại: `http://localhost:7070`

## 📡 API Endpoints

### POST /chat

Gửi câu hỏi và nhận câu trả lời từ AI.

**Request:**

```json
{
  "message": "Biển cấm dừng xe là gì?"
}
```

**Response:**

```json
{
  "answer": "Biển cấm dừng xe là biển báo giao thông có hình tròn, nền màu đỏ, viền vàng. Biển này cấm tất cả các phương tiện dừng lại ở khu vực đặt biển..."
}
```

**Error Response:**

```json
{
  "error": "Thiếu message"
}
```

### GET /

Health check endpoint.

**Response:**

```text
Chatbox API ok
```

## 🎯 Tính năng

- ✅ Trả lời câu hỏi về lý thuyết lái xe
- ✅ Giải thích biển báo giao thông
- ✅ Phân tích câu hỏi thi bằng lái
- ✅ Hướng dẫn xử lý tình huống
- ✅ Tập trung vào luật giao thông Việt Nam
- ✅ Trả lời ngắn gọn, dễ hiểu (200-300 từ)

## 🔧 Cấu hình

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `GEMINI_API_KEY` | Google Gemini API Key | **Required** |
| `PORT` | Port để chạy server | `7070` |

### Customization

Prompt AI có thể tùy chỉnh trong `server.js`:

```javascript
const system = `Bạn là trợ lý AI...`;
```

## 📦 Dependencies

- `express` - Web framework
- `@google/generative-ai` - Google Gemini AI SDK
- `cors` - CORS middleware
- `dotenv` - Environment variables

## 🐛 Troubleshooting

### Lỗi: "GEMINI_API_KEY is not defined"

→ Tạo file `.env` và thêm API key vào.

### Lỗi: "Cannot find module"

→ Chạy `npm install` để cài đặt dependencies.

### Lỗi: "Port 7070 is already in use"

→ Đổi PORT trong `.env` hoặc tắt process đang dùng port 7070.

## 📝 License

MIT
