# 🔍 Gemini Model Names

Danh sách các model Gemini có thể sử dụng:

## ✅ Models phổ biến (được khuyến nghị):

1. **gemini-pro** - Model phổ biến nhất, ổn định
2. **gemini-1.5-pro** - Model mới hơn với khả năng tốt hơn
3. **gemini-1.5-flash** - Model nhanh hơn, tốn ít token hơn
4. **gemini-pro-vision** - Hỗ trợ xử lý hình ảnh

## 🔄 Cách đổi model:

Trong file `server.js`, dòng 76:
```javascript
// Option 1: Model phổ biến (khuyến nghị)
const model = genAI.getGenerativeModel({ model: 'gemini-pro' });

// Option 2: Model mới hơn
const model = genAI.getGenerativeModel({ model: 'gemini-1.5-pro' });

// Option 3: Model nhanh hơn
const model = genAI.getGenerativeModel({ model: 'gemini-1.5-flash' });
```

## ⚠️ Lưu ý:

- Một số model có thể không khả dụng tùy theo API key và region
- Model name có thể thay đổi theo thời gian
- Nếu gặp lỗi 404, thử model khác trong danh sách trên

## 📚 Tài liệu tham khảo:

- [Google AI Studio](https://makersuite.google.com/)
- [Gemini API Documentation](https://ai.google.dev/docs)

