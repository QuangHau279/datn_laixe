@extends('layouts.app')
@section('title','Học lý thuyết 600 câu GPLX')

@section('content')
  {{-- HERO --}}
  <section class="hero">
    <div class="card hero-left reveal">
      <span class="label">HỆ THỐNG ÔN THI GPLX Ô TÔ, XE MÁY</span>
      <h1 class="hero-title">Nâng cao kiến thức, tự tin vượt qua kỳ thi sát hạch</h1>
      <p class="hero-text">Bộ đề cập nhật theo luật mới. Có thống kê tiến độ học, thi thử theo hạng.</p>
    </div>

    <div class="card hero-right reveal">
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
  {{-- Ôn thi Lý thuyết --}}
  <a class="course reveal" href="{{ route('practice.cauhoi') }}">
    <div class="thumb img-holder ratio-1x1"
         style="background-image:url('{{ asset('images/icons/icons8-car-100.png') }}')">
    </div>
    <div><h4>Ôn Thi Lý Thuyết</h4><p>600 câu</p></div>
  </a>

  {{-- Ôn thi Mô phỏng --}}
  <a class="course reveal" href="{{ route('simulation') }}">
    <div class="thumb img-holder ratio-1x1"
         style="background-image:url('{{ asset('images/icons/path.png') }}')">
    </div>
    <div><h4>Ôn Thi Mô Phỏng</h4><p>120 câu</p></div>
  </a>

  {{-- Thực hành lái xe → tạm dẫn về trang trợ giúp/hướng dẫn --}}
  <a class="course reveal" href="{{ route('videothuchanh') }}">
    <div class="thumb img-holder ratio-1x1"
         style="background-image:url('{{ asset('images/icons/dashboard.png') }}')">
      
    </div>
    <div><h4>Các Tình Huống Thực Tế</h4><p>Hỗ trợ học lái xe</p></div>
  </a>

  {{-- Xe máy → cũng dẫn đến phần ôn tập (nếu bạn muốn tách riêng sau thì đổi route) --}}
  <a class="course reveal" href="{{ route('xemay') }}"> 
    <div class="thumb img-holder ratio-1x1"
         style="background-image:url('{{ asset('images/icons/icons8-motorbike-100-2.png') }}')">
     
    </div>
    <div><h4>Xe Máy</h4><p>250 câu</p></div>
  </a>

  {{-- Thi thử trực tuyến --}}
  <a class="course reveal" href="{{ route('thi.thu') }}">
    <div class="thumb img-holder ratio-1x1"
         style="background-image:url('{{ asset('images/icons/license.png') }}')">
      
    </div>
    <div><h4>Kiểm Tra Trực Tuyến</h4><p>20 bộ đề</p></div>
  </a>

  {{-- Các biển báo --}}
  <a class="course reveal" href="{{ route('bienbao') }}">
    <div class="thumb img-holder ratio-1x1"
         style="background-image:url('{{ asset('images/icons/police.png') }}')">
      
    </div>
    <div><h4>Các biển báo</h4><p>Có minh họa</p></div>
  </a>
</section>


{{-- THỐNG KÊ --}}
<section class="stats" id="stats">
  <div class="stat reveal"><div class="num js-stat" data-target="5" data-pad="2">05</div><div class="desc">Năm phát triển</div></div>
  <div class="stat reveal"><div class="num js-stat" data-target="1000" data-suffix="+">1000+</div><div class="desc">Học viên</div></div>
  <div class="stat reveal"><div class="num js-stat" data-target="99" data-suffix="+">99+</div><div class="desc">Phản hồi tốt</div></div>
  <div class="stat reveal"><div class="num js-stat" data-target="100" data-suffix="%">100%</div><div class="desc">Chất lượng</div></div>
</section>

  {{-- FORM ĐĂNG KÝ (demo) --}}
  {{-- FORM ĐĂNG KÝ --}}
<section class="lead" aria-label="Đăng ký tư vấn">
  <div class="lead-card reveal">
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
          <option>A1</option><option>B1</option><option>B</option><option>C1</option>
        </select>
      </div>

      <div class="field full">
        <button class="btn" id="btnSubmit" type="submit">Đăng ký</button>
      </div>
    </form>
  </div>
</section>
@endsection
