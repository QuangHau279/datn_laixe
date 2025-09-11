@extends('layouts.app')
@section('title','Học lý thuyết 600 câu GPLX')

@section('content')
  {{-- HERO --}}
  <section class="hero">
    <div class="card hero-left">
      <span class="label">HỆ THỐNG ÔN THI GPLX Ô TÔ, XE MÁY</span>
      <h1 class="hero-title">Nâng cao kiến thức, tự tin vượt qua kỳ thi sát hạch</h1>
      <p class="hero-text">Bộ đề cập nhật theo luật mới. Có thống kê tiến độ học, thi thử theo hạng.</p>
    </div>

    <div class="card hero-right">
      {{-- Ảnh hero: để file tại public/images/hero.jpg --}}
      <div class="img-holder ratio-16x9"
           style="background-image:url('{{ asset('images/hinhanh/banner1.png') }}')"
           aria-label="Ảnh banner">
        <noscript><img src="{{ asset('images/hero.jpg') }}" alt="ảnh banner" /></noscript>
      </div>
    </div>
  </section>

  {{-- CÁC KHÓA HỌC --}}
  <h2 class="section-title">Các Khóa Học</h2>
  <section class="courses" aria-label="Danh sách khóa học">
    <a class="course" href="{{ route('practice') }}">
      <div class="thumb img-holder ratio-1x1"
           style="background-image:url('{{ asset('images/courses/ly-thuyet.jpg') }}')">
        <span>IMG</span>
      </div>
      <div><h4>Ôn Thi Lý Thuyết</h4><p>600 câu</p></div>
    </a>

    <a class="course" href="{{ route('simulation') }}">
      <div class="thumb img-holder ratio-1x1"
           style="background-image:url('{{ asset('images/courses/mo-phong.jpg') }}')">
        <span>IMG</span>
      </div>
      <div><h4>Ôn Thi Mô Phỏng</h4><p>120 câu</p></div>
    </a>

    <a class="course" href="#">
      <div class="thumb img-holder ratio-1x1"
           style="background-image:url('{{ asset('images/courses/thuc-hanh.jpg') }}')">
        <span>IMG</span>
      </div>
      <div><h4>Thực Hành Lái Xe</h4><p>Hỗ trợ học lái xe</p></div>
    </a>

    <a class="course" href="#">
      <div class="thumb img-holder ratio-1x1"
           style="background-image:url('{{ asset('images/courses/xe-may.jpg') }}')">
        <span>IMG</span>
      </div>
      <div><h4>Xe Máy</h4><p>250 câu</p></div>
    </a>

    <a class="course" href="{{ route('practice') }}">
      <div class="thumb img-holder ratio-1x1"
           style="background-image:url('{{ asset('images/courses/thi-thu.jpg') }}')">
        <span>IMG</span>
      </div>
      <div><h4>Thi Thử Trực Tuyến</h4><p>20 bộ đề</p></div>
    </a>

    <a class="course" href="#">
      <div class="thumb img-holder ratio-1x1"
           style="background-image:url('{{ asset('images/courses/bien-bao.jpg') }}')">
        <span>IMG</span>
      </div>
      <div><h4>Các biển báo</h4><p>Có minh họa</p></div>
    </a>
  </section>

{{-- THỐNG KÊ --}}
<section class="stats" id="stats">
  <div class="stat">
    <div class="num js-stat" data-target="5" data-pad="2">05</div>
    <div class="desc">Năm phát triển</div>
  </div>
  <div class="stat">
    <div class="num js-stat" data-target="1000" data-suffix="+">1000+</div>
    <div class="desc">Học viên</div>
  </div>
  <div class="stat">
    <div class="num js-stat" data-target="99" data-suffix="+">99+</div>
    <div class="desc">Phản hồi tốt</div>
  </div>
  <div class="stat">
    <div class="num js-stat" data-target="100" data-suffix="%">100%</div>
    <div class="desc">Chất lượng</div>
  </div>
</section>

  {{-- CTA --}}
  <section class="card cta">
    <h3>HỌC LÁI XE THÀNH CÔNG</h3>
    <p>Nhận Hồ Sơ & Bổ Túc Tay Lái</p>
    <p>Nhận hồ sơ lái xe uy tín, thân thiện, chi phí rõ ràng. Cam kết học đến khi đậu lấy bằng.</p>
    <p><strong style="color:var(--brand-dark)">Liên hệ: 0981.6868.75 (Thầy Phúc)</strong></p>
  </section>

  {{-- FORM ĐĂNG KÝ (demo) --}}
  {{-- FORM ĐĂNG KÝ --}}
<section class="lead" aria-label="Đăng ký tư vấn">
  <div class="lead-card">
    <h3>Đăng Ký Nhận Ưu Đãi</h3>

    <form class="form lead-form" id="leadForm">
      @csrf
      <div class="field">
        <label for="name">Tên</label>
        <input id="name" name="name" placeholder="Nguyễn Văn A" required>
      </div>

      <div class="field">
        <label for="phone">SDT</label>
        <input id="phone" name="phone" placeholder="09xx xxx xxx" pattern="[0-9\s\+]{8,}" required>
      </div>

      <div class="field full">
        <label for="license">Hạng</label>
        <select id="license" name="license" required>
          <option value="" disabled selected>Chọn</option>
          <option>A1</option><option>B1</option><option>B2</option><option>C</option>
        </select>
      </div>

      <div class="field full">
        <button class="btn" id="btnSubmit" type="submit">Đăng ký</button>
      </div>
    </form>
  </div>
</section>
@endsection
