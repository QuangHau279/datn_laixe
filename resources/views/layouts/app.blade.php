<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#2aa7e1">
  <title>@yield('title','LyThuyetLaiXe.vn')</title>
  <link rel="stylesheet" href="{{ asset('css/main.css') }}">
  @stack('styles')
</head>
<body>
  <header class="site-header">
    <div class="container nav">
      <div class="brand">
        <div class="logo img-holder ratio-1x1"
             style="width:40px;border-radius:50%;
                    background-image:url('{{ asset('images/icons/Logo.png') }}')">
        </div>
        <a href="{{ route('home') }}">LYTHUYETLAIXE.VN</a>
      </div>
      <button class="btn-menu" id="btnMenu" aria-controls="menuRight" aria-expanded="false">
        <span class="hamburger"></span><span>Menu</span>
      </button>
    </div>
  </header>

  {{-- Offcanvas phải (đè lên 1 phần bên phải khi mở) --}}
  <aside id="menuRight" class="offcanvas" aria-hidden="true" role="dialog">
    <header class="container nav" style="height:60px">
      <span class="menu-title">Menu</span>
      <button class="btn-menu" id="btnCloseMenu" aria-label="Đóng menu">✕</button>
    </header>
    <nav>
      <a href="{{ route('home') }}">Trang chủ</a>
      <a href="{{ route('practice.cauhoi') }}">Ôn thi lý thuyết</a>
      <a href="{{ route('simulation') }}">Mô phỏng</a>
      <a href="{{ route('videothuchanh') }}">Thực hành lái xe</a>
      <a href="{{ route('xemay') }}">Ôn tập xe máy</a>
      <a href="{{ route('thi.thu') }}">Thi thử</a>
      <a href="{{ route('bienbao') }}">Biển báo</a>
    </nav>
  </aside>
  <div id="scrim" class="scrim" aria-hidden="true"></div>

  <main class="container">
    @yield('content')
  </main>

  <footer class="site-footer"
          style="--footer-bg: url('{{ asset('images/hinhanh/footer-bg.jpg') }}');
                 --footer-bg-opacity: .18;">
    <div class="container">
      <strong>LYTHUYETLAIXE.VN</strong><br>
      <small>Hotline: 0981.6688.75</small>
    </div>
  </footer>

  <script>
    // Set API URL cho chatbox
    (function() {
      const apiUrl = @json(config('services.chat.api_url', 'http://localhost:7070/chat'));
      window.chatApiUrl = apiUrl || 'http://localhost:7070/chat';
      console.log('[Chatbox Config] API URL set to:', window.chatApiUrl);
    })();
  </script>
  <script src="{{ asset('js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
