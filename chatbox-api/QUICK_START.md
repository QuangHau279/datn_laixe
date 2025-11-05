# 🚀 Quick Start - Khởi động Chatbox API

## Bước 1: Tạo file .env

Tạo file `.env` trong thư mục `chatbox-api/` với nội dung:

```env
GEMINI_API_KEY=AIzaSyAM8p6MHJunpAzxl_hxBu4_2VeeILMSjuQ
PORT=7070
```

**Cách tạo trên Windows:**

1. Mở Command Prompt hoặc PowerShell
2. Di chuyển đến thư mục `chatbox-api`:
   ```cmd
   cd D:\PHPLavarel\datn_laixe\chatbox-api
   ```

3. Tạo file .env:
   ```cmd
   echo GEMINI_API_KEY=AIzaSyAM8p6MHJunpAzxl_hxBu4_2VeeILMSjuQ > .env
   echo PORT=7070 >> .env
   ```

4. Kiểm tra file đã tạo:
   ```cmd
   type .env
   ```

**Hoặc tạo thủ công:**
- Tạo file mới tên `.env` trong thư mục `chatbox-api/`
- Copy nội dung ở trên vào file
- Lưu file

## Bước 2: Cài đặt dependencies (nếu chưa có)

```bash
cd chatbox-api
npm install
```

## Bước 3: Khởi động server

```bash
npm start
```

**Hoặc chạy ở chế độ development (auto-reload):**
```bash
npm run dev
```

## Bước 4: Kiểm tra server đã chạy

Khi server khởi động thành công, bạn sẽ thấy:

```
==================================================
🤖 Chatbox API Server
==================================================
✅ Server đang chạy tại: http://localhost:7070
✅ Health check: http://localhost:7070/
✅ Chat endpoint: http://localhost:7070/chat
📝 GEMINI_API_KEY: ✓ Đã cấu hình
==================================================
```

**Test nhanh:**
- Mở browser: http://localhost:7070/
- Hoặc dùng curl:
  ```bash
  curl http://localhost:7070/
  ```

## Bước 5: Test API Chat

```bash
curl -X POST http://localhost:7070/chat ^
  -H "Content-Type: application/json" ^
  -d "{\"message\":\"Biển cấm là gì?\"}"
```

**Trên PowerShell:**
```powershell
curl -X POST http://localhost:7070/chat `
  -H "Content-Type: application/json" `
  -Body '{"message":"Biển cấm là gì?"}'
```

## 🔧 Troubleshooting

### Lỗi: "GEMINI_API_KEY chưa được cấu hình"

**Nguyên nhân:** File `.env` không tồn tại hoặc không được đọc đúng.

**Giải pháp:**
1. Kiểm tra file `.env` có trong thư mục `chatbox-api/` không
2. Kiểm tra nội dung file có đúng format không (không có khoảng trắng thừa)
3. Đảm bảo file `.env` nằm cùng thư mục với `server.js`
4. Restart server sau khi tạo/sửa file `.env`

### Lỗi: "Cannot find module 'dotenv'"

**Giải pháp:**
```bash
cd chatbox-api
npm install
```

### Lỗi: "Port 7070 is already in use"

**Giải pháp:**
1. Tìm process đang dùng port 7070:
   ```cmd
   netstat -ano | findstr :7070
   ```
2. Kill process đó hoặc đổi PORT trong file `.env`

## ✅ Checklist

- [ ] File `.env` đã được tạo trong `chatbox-api/`
- [ ] File `.env` có `GEMINI_API_KEY` và `PORT`
- [ ] Đã chạy `npm install`
- [ ] Server khởi động không có lỗi
- [ ] Test health check: http://localhost:7070/ trả về OK
- [ ] Laravel `.env` có `CHAT_API_URL=http://localhost:7070/chat`

## 📝 File .env mẫu

```env
# Gemini API Key từ Google AI Studio
GEMINI_API_KEY=AIzaSyAM8p6MHJunpAzxl_hxBu4_2VeeILMSjuQ

# Port cho chatbox API (default: 7070)
PORT=7070
```

**Lưu ý:** 
- Không có khoảng trắng trước/sau dấu `=`
- Không có dấu ngoặc kép quanh giá trị
- Mỗi biến trên một dòng riêng

