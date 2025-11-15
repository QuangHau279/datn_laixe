# 🔒 Session Limit Configuration

Hệ thống giới hạn số câu hỏi mỗi session để bảo vệ API key free tier.

## 📋 Cấu hình trong file `.env`

Thêm các dòng sau vào file `chatbox-api/.env`:

```env
# Giới hạn số câu hỏi mỗi session (mặc định: 10)
SESSION_LIMIT=10

# Thời gian hết hạn session (giây, mặc định: 3600 = 1 giờ)
SESSION_TIMEOUT=3600

# Bật giới hạn theo IP để tránh bypass cookie (mặc định: false)
ENABLE_IP_LIMIT=false
```

## 🎯 Cách hoạt động

1. **Session Cookie**: Mỗi user được gán một session ID lưu trong cookie
2. **In-memory Storage**: Server lưu số lần hỏi trong RAM
3. **Tự động Reset**: Session tự động reset sau `SESSION_TIMEOUT` giây
4. **Cleanup**: Server tự động xóa session hết hạn mỗi 5 phút

## 📊 Response Format

### Thành công:
```json
{
  "answer": "Câu trả lời từ AI...",
  "remaining": 7,
  "limit": 10,
  "resetAt": "2025-11-15T14:30:00.000Z"
}
```

### Hết lượt (429):
```json
{
  "error": "Đã đạt giới hạn số câu hỏi",
  "message": "Đã đạt giới hạn số câu hỏi trong session này",
  "remaining": 0,
  "limit": 10,
  "resetAt": "2025-11-15T14:30:00.000Z",
  "suggestion": "Bạn đã sử dụng hết 10 câu hỏi trong session này. Vui lòng quay lại sau 30 phút."
}
```

## 🔍 Endpoints

### GET `/session`
Xem thông tin session hiện tại:
```json
{
  "hasSession": true,
  "remaining": 7,
  "used": 3,
  "limit": 10,
  "resetAt": "2025-11-15T14:30:00.000Z",
  "createdAt": "2025-11-15T13:30:00.000Z"
}
```

## ⚙️ Tùy chọn nâng cao

### Bật IP Limit
Nếu muốn giới hạn theo IP (tránh bypass cookie):
```env
ENABLE_IP_LIMIT=true
```

**Lưu ý**: 
- Nhiều user cùng IP sẽ dùng chung quota
- Có thể ảnh hưởng đến user trong mạng LAN

### Điều chỉnh giới hạn
```env
# Giảm xuống 5 câu/session
SESSION_LIMIT=5

# Tăng timeout lên 2 giờ
SESSION_TIMEOUT=7200
```

## 🛡️ Bảo vệ API Key

Hệ thống này giúp:
- ✅ Giới hạn số request mỗi user
- ✅ Tự động reset sau thời gian
- ✅ Tránh lạm dụng API key free tier
- ✅ Hiển thị thông báo rõ ràng cho user

## 📝 Lưu ý

- Session lưu trong RAM → mất khi restart server
- Cookie tự động hết hạn theo `SESSION_TIMEOUT`
- Frontend tự động hiển thị số câu còn lại khi ≤ 3 câu

