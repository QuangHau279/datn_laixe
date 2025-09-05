<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học lý thuyết 600 câu</title>
    <link rel="stylesheet" href="{{ asset('css/quiz-style.css') }}">
</head>
<body>

    <div class="main-container">
        <header>
            <h1>Học lý thuyết 600 câu lái xe ô tô Online | Luật mới</h1>
        </header>

        <div class="quiz-wrapper">
            <div class="question-grid"></div>

            <div class="question-area">
                <div id="question-container">
                    <p>Đang tải câu hỏi aaaa...</p>
                </div>

                <div class="navigation-buttons">
                    <button id="prev-btn" class="nav-btn" disabled>&lt;&lt; Câu trước</button>
                    <button id="next-btn" class="nav-btn" disabled>Câu sau &gt;&gt;</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Cấu hình endpoint JSON để JS fetch dữ liệu câu hỏi + ảnh --}}
    <script>
        window.QUESTION_API = "{{ url('/cauhoi/json') }}";
    </script>

    {{-- Liên kết tới file Javascript đã được viết lại --}}
    <script src="{{ asset('js/quiz-logic.js') }}"></script>
</body>
</html>
