# 🚗 LyThuyetLaiXe.vn - Hệ thống ôn thi GPLX

Hệ thống ôn thi giấy phép lái xe (GPLX) với đầy đủ tính năng: ôn tập 600 câu lý thuyết, thi thử trực tuyến, thi mô phỏng, và **trợ lý AI chatbot** hỗ trợ học tập 24/7.

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
</p>

## ✨ Tính năng

- ✅ **Ôn tập lý thuyết**: 600 câu hỏi với hình ảnh minh họa
- ✅ **Thi thử trực tuyến**: 20 bộ đề theo chuẩn thi thật
- ✅ **Thi mô phỏng**: 120 tình huống giao thông thực tế
- ✅ **Biển báo giao thông**: Hướng dẫn đầy đủ các loại biển báo
- ✅ **🤖 Trợ lý AI Chatbot**: Hỏi đáp về lý thuyết lái xe 24/7
- ✅ **Responsive**: Tối ưu cho mobile, tablet, desktop

## 🚀 Hướng dẫn cài đặt

### 1. Cài đặt Laravel

```bash
# Clone repository
git clone <repo-url>
cd datn_laixe

# Cài đặt dependencies
composer install
npm install

# Tạo file .env
cp .env.example .env
php artisan key:generate

# Chạy migration và seeder
php artisan migrate --seed
```

### 2. Cài đặt Chatbox AI

Chatbox sử dụng Google Gemini AI để trả lời câu hỏi về lý thuyết lái xe.

```bash
# Vào thư mục chatbox-api
cd chatbox-api

# Cài đặt Node.js dependencies
npm install

# Tạo file .env
echo "GEMINI_API_KEY=your_gemini_api_key_here" > .env
echo "PORT=7070" >> .env

# Khởi động chatbox API
npm start
```

**Lấy Gemini API Key:**
1. Truy cập [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Đăng nhập bằng Google account
3. Tạo API key mới
4. Copy key vào file `.env` của `chatbox-api/`

### 3. Cấu hình Laravel .env

Thêm cấu hình chatbox API vào `.env`:

```env
CHAT_API_URL=http://localhost:7070/chat
```

### 4. Khởi động ứng dụng

```bash
# Terminal 1: Khởi động Laravel
php artisan serve

# Terminal 2: Khởi động Chatbox API (nếu chưa chạy)
cd chatbox-api
npm start
```

Truy cập: `http://localhost:8000`

## 💬 Chatbox AI

Chatbox floating xuất hiện ở mọi trang, cho phép người dùng:

- Hỏi về lý thuyết lái xe
- Giải thích biển báo giao thông
- Phân tích câu hỏi thi
- Hướng dẫn tình huống mô phỏng

**Prompt AI được tối ưu** để tập trung vào:
- Luật giao thông đường bộ Việt Nam
- 600 câu hỏi lý thuyết lái xe
- Xử phạt vi phạm giao thông
- An toàn lái xe

## 📁 Cấu trúc dự án

```
datn_laixe/
├── app/                    # Laravel MVC
│   ├── Http/Controllers/   # Controllers
│   ├── Models/             # Eloquent Models
│   └── ...
├── chatbox-api/            # Node.js AI Chatbot
│   ├── server.js           # Express API server
│   └── package.json
├── database/               # Migrations & Seeders
├── public/                 # Public assets
│   ├── css/main.css        # Styles (kèm chatbox)
│   └── js/main.js          # JavaScript (kèm chatbox)
├── resources/views/        # Blade templates
│   └── layouts/app.blade.php  # Main layout với chatbox
└── routes/web.php          # Web routes
```

## 📝 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
