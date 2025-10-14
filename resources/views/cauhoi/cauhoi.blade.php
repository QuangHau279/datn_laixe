@extends('layouts.app')
@section('title','Ôn tập')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/quiz-style.css') }}">
@endpush

@section('content')
<div class="container quiz-page">
  <div class="quiz-banner img-holder ratio-16x9"
       style="background-image:url('{{ asset('images/hinhanh/banner1.png') }}')"></div>

  <h1 class="quiz-heading">Xe ô tô | Luật mới</h1>

  <div class="quiz-layout quiz-wrapper">
    {{-- SIDEBAR --}}
    <aside class="quiz-sidebar">
      <div class="sidebar-card">
        <label class="sidebar-label">Tìm kiếm</label>
        <input id="qSearch" class="sidebar-input" placeholder="Nhập số câu... (1–600)">
      </div>

      <div class="question-grid">
        @for ($i = 1; $i <= 600; $i++)
          <a class="question-number"
             href="{{ url('/on-tap/cau-hoi/'.$i) }}"
             @if(isset($initialStt) && (int)$initialStt === $i) aria-current="page" @endif>
            {{ $i }}
          </a>
        @endfor
      </div>

      <div class="sidebar-actions">
        <a class="pill" href="{{ url('/on-tap/cau-hoi/1') }}">Thi thử đề thi</a>
        <a class="pill" href="{{ url('/on-tap/cau-hoi') }}">20 bộ đề</a>
      </div>
    </aside>

    {{-- MAIN --}}
    <section class="quiz-main">
      <div class="question-card">
        <div id="question-container"><p>Đang tải câu hỏi…</p></div>
        <div class="navigation-buttons">
          <button id="prev-btn" class="nav-btn" disabled>&lt;&lt; Câu trước</button>
          <button id="next-btn" class="nav-btn" disabled>Câu sau &gt;&gt;</button>
        </div>
      </div>
    </section>
  </div>

  <div class="quiz-extra img-holder" style="height:340px">
    <span>Nội dung thêm sau</span>
  </div>
</div>
@endsection

@push('scripts')
  <script>
    window.QUIZ_CONFIG = {
      apiBase: "{{ url('/api/cau-hoi') }}",
      initialStt: {{ $initialStt ?? 'null' }}
    };
    window.QUESTION_API = window.QUIZ_CONFIG.apiBase;

    // Tìm số câu và nhảy tới khi Enter
    (function(){
      const base = "{{ url('/on-tap/cau-hoi') }}";
      const ip = document.getElementById('qSearch');
      ip?.addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
          const n = parseInt(ip.value, 10);
          if (n >= 1 && n <= 600) window.location = base + '/' + n;
        }
      });
    })();
  </script>
  <script src="{{ asset('js/quiz-logic.js') }}" defer></script>
@endpush
