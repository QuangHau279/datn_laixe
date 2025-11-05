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

  /* ===== Search Form ===== */
  .yt-search-section{
    margin-bottom:20px;
  }
  .yt-search-form{
    display:flex;
    gap:8px;
    margin-bottom:12px;
  }
  .yt-search-input{
    flex:1;
    padding:10px 14px;
    border:1px solid #dbe7f2;
    border-radius:10px;
    font-size:14px;
    outline:none;
  }
  .yt-search-input:focus{
    border-color:#2aa7e1;
    box-shadow:0 0 0 3px rgba(42,167,225,0.1);
  }
  .yt-search-btn{
    padding:10px 20px;
    background:linear-gradient(135deg,#2aa7e1,#35b2f6);
    color:white;
    border:none;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    transition:transform 0.2s,box-shadow 0.2s;
  }
  .yt-search-btn:hover{
    transform:translateY(-1px);
    box-shadow:0 4px 12px rgba(42,167,225,0.3);
  }
  .yt-search-btn:active{
    transform:translateY(0);
  }
  .yt-keyword-display{
    display:flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    background:#f0f7ff;
    border-radius:8px;
    font-size:13px;
  }
  .yt-keyword-display strong{
    color:#2aa7e1;
  }
  .yt-keyword-remove{
    padding:4px 8px;
    background:#fff;
    border:1px solid #dbe7f2;
    border-radius:6px;
    cursor:pointer;
    font-size:11px;
    color:#64748b;
    text-decoration:none;
    transition:all 0.2s;
  }
  .yt-keyword-remove:hover{
    background:#ffe6e6;
    border-color:#ff6b6b;
    color:#ff4444;
  }
  .yt-no-results{
    padding:20px;
    text-align:center;
    color:#64748b;
    background:#f8fafc;
    border-radius:10px;
    margin:20px 0;
  }

  @media (max-width:1024px){
    .yt-wrap{ grid-template-columns:1fr }
    .yt-list{ max-height:none }
    .yt-search-form{ flex-direction:column }
    .yt-search-btn{ width:100% }
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
    {{-- ===== Search Form ===== --}}
    <div class="yt-search-section">
      <form class="yt-search-form" method="GET" action="{{ route('videothuchanh') }}">
        @if(request('v'))
          <input type="hidden" name="v" value="{{ request('v') }}">
        @endif
        <input 
          type="text" 
          name="q" 
          class="yt-search-input" 
          placeholder="Tìm kiếm video (ví dụ: đỗ xe, lùi xe, ghép ngang...)" 
          value="{{ $keyword }}"
          autocomplete="off"
        >
        <button type="submit" class="yt-search-btn">🔍 Tìm kiếm</button>
      </form>

      {{-- Hiển thị keyword đang tìm kiếm --}}
      @if($keyword)
        <div class="yt-keyword-display">
          <span>📝 Đang tìm: <strong>{{ $keyword }}</strong></span>
          <span>({{ $totalResults }} kết quả)</span>
          <a href="{{ route('videothuchanh', request()->only('v')) }}" class="yt-keyword-remove">
            ✕ Xóa bộ lọc
          </a>
        </div>
      @endif
    </div>

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
          @if($keyword)
            <div class="yt-no-results">
              <p>Không tìm thấy video nào với từ khóa "<strong>{{ $keyword }}</strong>"</p>
              <p style="margin-top:8px">
                <a href="{{ route('videothuchanh') }}" style="color:#2aa7e1;text-decoration:underline">
                  Xem tất cả video
                </a>
              </p>
            </div>
          @else
            <p>Không tìm thấy video nào.</p>
          @endif
        @endif
      </section>

      {{-- ===== Danh sách video phụ ===== --}}
      <aside class="yt-list">
        <h3 style="margin:6px 8px 12px">
          @if($keyword)
            Kết quả tìm kiếm ({{ $totalResults }})
          @else
            Video khác ({{ count($others) }})
          @endif
        </h3>
        
        @if(empty($others) && $keyword)
          <div class="yt-no-results">
            <p>Không tìm thấy video nào với từ khóa "<strong>{{ $keyword }}</strong>"</p>
            <p style="margin-top:8px;font-size:12px">
              <a href="{{ route('videothuchanh', request()->only('v')) }}" style="color:#2aa7e1">
                Xem tất cả video
              </a>
            </p>
          </div>
        @else
          @foreach($others as $v)
            @php
              $hrefParams = ['v' => $v['id']];
              if($keyword) {
                $hrefParams['q'] = $keyword;
              }
            @endphp
            <a class="yt-item" href="{{ route('videothuchanh', $hrefParams) }}" aria-label="{{ $v['title'] }}">
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
        @endif
      </aside>

    </div>
  @endif
@endsection
