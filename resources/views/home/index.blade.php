@extends('layouts.app')
@section('title','Học lý thuyết 600 câu GPLX')

@section('content')
  <section class="hero">
    <div class="card hero-left">
      <span class="label">HỆ THỐNG ÔN THI GPLX Ô TÔ, XE MÁY</span>
      <h1 class="hero-title">Nâng cao kiến thức, tự tin vượt qua kỳ thi sát hạch</h1>
      <p class="hero-text">Bộ đề cập nhật theo luật mới. Có thống kê tiến độ học, thi thử theo hạng.</p>
    </div>
 <div class="card hero-right">
  {{-- Khung ảnh hero để trống, thay sau bằng background-image --}}
  <div class="img-holder ratio-16x9" aria-label="Vị trí ảnh hero (để trống)">
    <span class="note-badge">Thay ảnh: public/images/hero.jpg</span>
    <span>Để trống — thêm sau</span>
  </div>
</div>

  </section>

  <h2 class="section-title">Các Khóa Học</h2>
    <section class="courses">
    <article class="course">
        <div class="thumb img-holder ratio-1x1" aria-label="Ảnh Lý thuyết (để trống)">
        <span>IMG</span>
        </div>
        <div><h4>Ôn Thi Lý Thuyết</h4><p>600 câu</p></div>
    </article>

    <article class="course">
        <div class="thumb img-holder ratio-1x1" aria-label="Ảnh Mô phỏng (để trống)">
        <span>IMG</span>
        </div>
        <div><h4>Ôn Thi Mô Phỏng</h4><p>120 câu</p></div>
    </article>

    <article class="course">
        <div class="thumb img-holder ratio-1x1" aria-label="Ảnh Thực hành (để trống)">
        <span>IMG</span>
        </div>
        <div><h4>Thực Hành Lái Xe</h4><p>Hỗ trợ học lái xe</p></div>
    </article>
    <article class="course">
        <div class="thumb img-holder ratio-1x1" aria-label="Ảnh Hỗ trợ (để trống)">
        <span>IMG</span>
        </div>
        <div><h4>Hỗ Trợ Tư Vấn</h4><p>Hồ sơ, thủ tục</p></div>
        </article>
        <article class="course">
        <div class="thumb img-holder ratio-1x1" aria-label="Ảnh Xem thêm (để trống)">
            <span>Bộ thi thử</span>
        </div>
        <div><h4>Thi Thử Trực Tuyến</h4><p>20 bộ đề</p></div>
    </article>
    <article class="course">
        <div class="thumb img-holder ratio-1x1" aria-label="Ảnh Xem thêm (để trống)">
            <span>Biển báo</span>
        </div>
        <div><h4>Các biển báo</h4></div>
    </section>

  <section class="stats">
    <div class="stat"><div class="num">05</div><div class="desc">Năm phát triển</div></div>
    <div class="stat"><div class="num">1000+</div><div class="desc">Học viên</div></div>
    <div class="stat"><div class="num">99+</div><div class="desc">Phản hồi tốt</div></div>
    <div class="stat"><div class="num">100%</div><div class="desc">Chất lượng</div></div>
  </section>

  <section class="card cta">
    <h3>HỌC LÁI XE THÀNH CÔNG</h3>
    <p>Nhận Hồ Sơ & Bổ Túc Tay Lái</p>
      <p>  Nhận hồ sơ lái xe uy tín, thân thiện, chi phí rõ ràng. Cam kết học đến khi đậu lấy bằng. </p>
         <strong style="color:var(--brand-dark)">Xin gọi về: 0981.6868.75 (Thầy Phúc )</strong></p>
  </section>

  {{-- Form lead nếu cần nối DB sau này --}}
  <section class="lead">
    <h3>Đăng Ký Nhận Ưu Đãi</h3>
    <form class="form" id="leadForm">
      @csrf
      <div class="field"><label>Tên</label><input id="name" name="name" required></div>
      <div class="field"><label>SĐT</label><input id="phone" name="phone" pattern="[0-9\s\+]{8,}" required></div>
      <div class="field"><label>Hạng</label>
        <select id="license" name="license" required>
          <option value="" disabled selected>Chọn</option><option>A1</option><option>B1</option><option>B2</option><option>C</option>
        </select>
      </div>
      <div class="field full"><button class="btn" id="btnSubmit">Đăng ký</button></div>
    </form>
  </section>
@endsection
