<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Học lý thuyết 600 câu</title>
  <link rel="stylesheet" href="{{ asset('css/quiz-style.css') }}">
</head>
<body>
  <div class="page">
    <header class="site-header">
      <div class="brand">LYTHUYETLAIXE.VN</div>
      <nav class="breadcrumb">Trang Chủ / Học lý thuyết 600 câu lái xe ô tô Online | Luật mới</nav>
    </header>

    <section class="hero">
      <h1><span class="hl">Học lý thuyết 600 câu lái xe ô tô Online | Luật mới</span></h1>
    </section>

    <main class="layout">
      <!-- Cột trái -->
      <aside class="left">
        <div class="card">
          <div class="search-row">
            <label for="kw">Tìm kiếm</label>
            <input id="kw" type="text" placeholder="Nhập câu hỏi" />
          </div>

          <div class="number-grid" id="questionGrid"><!-- JS render --></div>

          <div class="left-actions">
            <button class="btn btn-yellow" id="btnMock">Thi thử đề thi</button>
            <button class="btn btn-gray"   id="btn20">20 bộ đề</button>
          </div>
        </div>
      </aside>

      <!-- Cột phải -->
      <section class="right">
        <div class="card qcard">
          <div id="question-container">
            <p>Đang tải câu hỏi...</p>
          </div>

          <div class="nav-row">
            <button id="prev-btn" class="btn btn-blue" disabled>&laquo;&laquo; Câu trước</button>
            <div class="hint">Bạn có thể sử dụng phím số 1–4 để trả lời nhanh • &larr;/&rarr; để đổi câu</div>
            <button id="next-btn" class="btn btn-blue" disabled>Câu sau &raquo;&raquo;</button>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>window.__QUESTIONS__ = @json($ds);</script>
  <script src="{{ asset('js/quiz-logic.js') }}"></script>
</body>
</html>
