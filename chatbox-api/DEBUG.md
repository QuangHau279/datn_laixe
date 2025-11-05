# 🔍 Hướng dẫn Debug Chatbox

## Kiểm tra các vấn đề phổ biến

### 1. API URL không đúng

**Triệu chứng:** Console hiển thị lỗi "Failed to fetch" hoặc "NetworkError"

**Kiểm tra:**

1. Mở browser console (F12) và kiểm tra:
```javascript
console.log(window.chatApiUrl);
// Nên hiển thị: "http://localhost:7070/chat" hoặc URL bạn đã cấu hình
```

2. Kiểm tra file `.env` trong Laravel:
```env
CHAT_API_URL=http://localhost:7070/chat
```

3. Kiểm tra `config/services.php`:
```php
'chat' => [
    'api_url' => env('CHAT_API_URL', 'http://localhost:7070/chat'),
]
```

**Giải pháp:**
- Đảm bảo `.env` có `CHAT_API_URL`
- Clear config cache: `php artisan config:clear`
- Restart Laravel server

### 2. Server Chatbox API chưa chạy

**Triệu chứng:** Lỗi "Failed to fetch" hoặc không thể kết nối

**Kiểm tra:**

1. Mở terminal trong `chatbox-api/`:
```bash
cd chatbox-api
npm start
```

2. Kiểm tra server đang chạy:
```bash
curl http://localhost:7070/
# Hoặc mở trình duyệt: http://localhost:7070/
```

**Giải pháp:**
- Khởi động server: `npm start` hoặc `npm run dev`
- Kiểm tra port 7070 có bị chiếm không: `netstat -ano | findstr :7070` (Windows)

### 3. Lỗi CORS

**Triệu chứng:** Console hiển thị lỗi CORS policy

**Kiểm tra:**
- Server `chatbox-api/server.js` đã cấu hình CORS chưa
- Browser console có lỗi CORS không

**Giải pháp:**
- Đảm bảo `chatbox-api/server.js` có:
```javascript
app.use(cors({
  origin: '*',
  methods: ['GET', 'POST', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
}));
```

### 4. GEMINI_API_KEY chưa có

**Triệu chứng:** Server trả lỗi 500 hoặc "API key not found"

**Kiểm tra:**

1. File `.env` trong `chatbox-api/`:
```env
GEMINI_API_KEY=your_api_key_here
```

2. Khởi động lại server và kiểm tra log:
```
📝 GEMINI_API_KEY: ✓ Đã cấu hình
```

**Giải pháp:**
- Lấy API key từ [Google AI Studio](https://makersuite.google.com/app/apikey)
- Thêm vào `.env` và restart server

### 5. JavaScript không load đúng

**Triệu chứng:** Chatbox không hiển thị hoặc không hoạt động

**Kiểm tra:**

1. Browser console (F12):
```javascript
// Kiểm tra API URL
console.log(window.chatApiUrl);

// Kiểm tra chatbox element
console.log(document.getElementById('chatbox-window'));
```

2. Kiểm tra file `public/js/main.js` có được load:
- Network tab xem request `main.js` có 200 không
- Console có lỗi JavaScript không

**Giải pháp:**
- Clear browser cache: Ctrl+F5
- Kiểm tra path file JavaScript trong layout
- Kiểm tra console có lỗi không

## Debug Steps

### Step 1: Kiểm tra Server

```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Chatbox API
cd chatbox-api
npm start
```

### Step 2: Kiểm tra Browser Console

1. Mở trang web (http://localhost:8000)
2. Mở Developer Tools (F12)
3. Tab Console - tìm log:
   ```
   [Chatbox Config] API URL set to: http://localhost:7070/chat
   [Chatbox] API URL: http://localhost:7070/chat
   ```

### Step 3: Test API trực tiếp

Mở browser và test:
```bash
# Health check
curl http://localhost:7070/

# Test chat
curl -X POST http://localhost:7070/chat \
  -H "Content-Type: application/json" \
  -d '{"message":"Biển cấm là gì?"}'
```

Hoặc dùng Postman/Thunder Client

### Step 4: Kiểm tra Network Tab

1. Mở Network tab (F12)
2. Gửi tin nhắn trong chatbox
3. Kiểm tra request đến `/chat`:
   - Status code: 200 OK
   - Response có field `answer`
   - Không có lỗi CORS

## Log Messages

### Client-side (Browser Console):

- `[Chatbox Config] API URL set to: ...` - API URL đã được set
- `[Chatbox] API URL: ...` - URL được sử dụng
- `[Chatbox] Sending message to: ...` - Đang gửi request
- `[Chatbox] Response status: ...` - Status code nhận được
- `[Chatbox] Response data: ...` - Dữ liệu response

### Server-side (Terminal):

- `[Chat API] Received request: ...` - Nhận được request
- `[Chat API] Processing message: ...` - Đang xử lý
- `[Chat API] Response generated, length: ...` - Đã tạo response
- `[Chat API] Error: ...` - Có lỗi xảy ra

## Common Error Messages

| Error | Nguyên nhân | Giải pháp |
|-------|-------------|-----------|
| `Failed to fetch` | Server chưa chạy hoặc URL sai | Khởi động server chatbox-api |
| `CORS policy error` | CORS chưa config | Kiểm tra server.js có cors() |
| `API key not found` | GEMINI_API_KEY thiếu | Thêm vào .env và restart |
| `Cannot read property 'answer'` | Response format sai | Kiểm tra server response |
| `NetworkError` | Kết nối bị chặn | Kiểm tra firewall/antivirus |

## Test Checklist

- [ ] Server chatbox-api đang chạy (port 7070)
- [ ] File `.env` trong `chatbox-api/` có `GEMINI_API_KEY`
- [ ] File `.env` trong Laravel có `CHAT_API_URL`
- [ ] Browser console không có lỗi
- [ ] Network tab thấy request đến `/chat` với status 200
- [ ] Response có field `answer`

