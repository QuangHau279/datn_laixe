@extends('layouts.app')
@section('title','Thực hành lái xe')

@push('styles')
<style>
  /* ===== Layout chung ===== */
  .yt-wrap{
    display:grid;
    grid-template-columns:1fr 360px;
    gap:24px;
  }
  .yt-main{
    background:#fff;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(15,23,42,.06);
    padding:16px;
  }

  /* ===== Khung video chính (CÔ LẬP, tránh xung đột) ===== */
  .yt-wrap{ padding-top:0 !important; }              /* chặn mọi rule toàn cục */
  .yt-main .yt-frame{
    position:relative;
    width:100%;
    aspect-ratio:16/9;                                /* chuẩn 16:9, KHÔNG dùng padding-top */
    border-radius:12px;
    overflow:hidden;
    max-width:960px;                                  /* giới hạn bề rộng video */
    margin:0 auto;
    background:#000;
    padding-top:0 !important;                         /* chặn rule cũ nếu có */
  }
  .yt-main .yt-frame > iframe{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    border:0;
    display:block;
  }

  /* ===== Danh sách video phụ ===== */
  .yt-list{
    background:#fff;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(15,23,42,.06);
    padding:12px;
    max-height:calc(100vh - 160px);
    overflow:auto;
  }
  .yt-item{
    display:grid;
    grid-template-columns:120px 1fr;
    gap:10px;
    padding:8px;
    border-radius:10px;
  }
  .yt-item:hover{ background:#f8fafc }
  .yt-thumb{
    width:120px; height:68px;
    border-radius:8px; object-fit:cover;
  }
  .yt-title{ font-weight:600; font-size:14px; line-height:1.3 }
  .yt-date{ font-size:12px; color:#64748b; margin-top:4px }

  @media (max-width:1024px){
    .yt-wrap{ grid-template-columns:1fr }
    .yt-list{ max-height:none }
  }
</style>
@endpush

@section('content')
  <h1 class="section-title">Thực hành lái xe</h1>

  @if($error)
    <div class="card" style="padding:12px;background:#fff3cd;border:1px solid #ffeeba;border-radius:8px">
      <strong>Lỗi:</strong> {{ $error }}
    </div>
  @else
    <div class="yt-wrap">

      {{-- ===== Video chính ===== --}}
      <section class="yt-main">
        @if($main)
          @php
            $videoId = $main['id'] ?? null;
            $title   = $main['title'] ?? 'YouTube Video';
            $origin  = request()->getSchemeAndHttpHost();
          @endphp

          <div class="yt-frame">
            <iframe
              title="{{ $title }}"
              src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0&modestbranding=1&playsinline=1&enablejsapi=1&origin={{ $origin }}"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
              referrerpolicy="strict-origin-when-cross-origin"
              style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block"
            ></iframe>
          </div>

          <h2 style="margin-top:12px">{{ $main['title'] }}</h2>

          @if(!empty($main['publishedAt']))
            <div class="yt-date">
              Đăng ngày {{ \Carbon\Carbon::parse($main['publishedAt'])->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
            </div>
          @endif

          @if(!empty($main['desc']))
            <p style="margin-top:8px;white-space:pre-line">
              {{ \Illuminate\Support\Str::limit($main['desc'], 300) }}
            </p>
          @endif
        @else
          <p>Không tìm thấy video nào.</p>
        @endif
      </section>

      {{-- ===== Danh sách video phụ ===== --}}
      <aside class="yt-list">
        <h3 style="margin:6px 8px 12px">Video khác</h3>
        @foreach($others as $v)
          <a class="yt-item" href="{{ route('videothuchanh', ['v' => $v['id']]) }}" aria-label="{{ $v['title'] }}">
            <img class="yt-thumb" src="{{ $v['thumb'] }}" alt="{{ $v['title'] }}">
            <div>
              <div class="yt-title">{{ $v['title'] }}</div>
              @if(!empty($v['publishedAt']))
                <div class="yt-date">
                  {{ \Carbon\Carbon::parse($v['publishedAt'])->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}
                </div>
              @endif
            </div>
          </a>
        @endforeach
      </aside>

    </div>
  @endif
@endsection
