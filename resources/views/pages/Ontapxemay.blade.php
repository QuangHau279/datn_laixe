@extends('layouts.app')
@section('title','Ôn tập')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/quiz-style.css') }}">
  <style>
    .search-results {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      max-height: 300px;
      overflow-y: auto;
      z-index: 1000;
      margin-top: 4px;
    }
    .search-result-item {
      padding: 10px 15px;
      cursor: pointer;
      border-bottom: 1px solid #f0f0f0;
      transition: background 0.2s;
    }
    .search-result-item:hover {
      background: #f5f5f5;
    }
    .search-result-item:last-child {
      border-bottom: none;
    }
    .search-result-stt {
      font-weight: bold;
      color: #007bff;
      margin-right: 8px;
    }
    .search-result-snippet {
      color: #666;
      font-size: 0.9em;
      margin-top: 4px;
    }
    .search-no-results {
      padding: 15px;
      text-align: center;
      color: #999;
    }
  </style>
@endpush

@section('content')
<div class="container quiz-page">
  <div class="quiz-banner img-holder ratio-16x9"
       style="background-image:url('{{ asset('images/hinhanh/banner1.png') }}')"></div>

  <h1 class="quiz-heading">Xe máy | Luật mới</h1>

  <div class="quiz-layout quiz-wrapper">
    {{-- SIDEBAR --}}
    <aside class="quiz-sidebar">
      <div class="sidebar-card" style="position: relative;">
        <label class="sidebar-label">Tìm kiếm</label>
        <input id="qSearch" class="sidebar-input" placeholder="Số câu (1-250) hoặc từ khóa...">
        <div id="search-results" class="search-results" style="display: none;"></div>
      </div>

      <div class="question-grid">
        {{-- Grid sẽ được render bởi JavaScript từ API --}}
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
      apiBase: "{{ url('/api/xe-may') }}",
      initialStt: {{ $initialStt ?? 'null' }},
      isXeMay: true,
      maxQuestions: 250
    };
    window.QUESTION_API = window.QUIZ_CONFIG.apiBase;

    // Tìm kiếm sẽ được xử lý trong quiz-logic-xemay.js
  </script>
  <script src="{{ asset('js/quiz-logic-xemay.js') }}" defer></script>
@endpush
