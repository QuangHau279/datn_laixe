@extends('layouts.app')
@section('title','Thi thử theo hạng')

@push('styles')
  {{-- CSS riêng của trang thi --}}
  <link rel="stylesheet"
        href="{{ asset('css/thi-thu.css') }}?v={{ filemtime(public_path('css/thi-thu.css')) }}">
@endpush

@section('content')
  {{-- Tiêu đề trang --}}
  <div class="page-title">Thi thử</div>

  {{-- Thanh thời gian --}}
  <div class="timebar">
    <span>Thời gian còn lại:</span>
    <strong id="timer">00:00</strong>
  </div>

  <div class="layout">
    {{-- LEFT --}}
    <aside class="left">
      <div class="hang-row">
        <label for="selHang">Hạng</label>
        <select id="selHang"></select>
        <button id="btnStart" type="button" class="btn btn-start">Bắt đầu</button>
      </div>

      <div class="subtime">Thời gian: <b id="tg">--</b> phút</div>

      <div id="grid" class="pad"></div>
      <div class="note">* Ô viền đỏ là câu liệt</div>

      <button id="btnSubmit" type="button" class="btn btn-end">Kết thúc</button>
    </aside>

    {{-- RIGHT --}}
    <main class="right card">
      {{-- Welcome --}}
      <div id="panelWelcome">
        <h3>Chọn hạng và đề thi</h3>
        <p class="muted">Sai <b>câu liệt</b> sẽ <b>rớt</b> dù đủ điểm.</p>
        <div id="deGroup" class="de-group"></div>
        <select id="selDe" style="display:none"></select>
      </div>

      {{-- Exam --}}
      <div id="panelExam" class="exam" style="display:none">
        <div class="exam-head">
          <div class="qindex">Câu <span id="idx">1</span>/<span id="total">--</span></div>
          <div class="muted small">Chọn xong vẫn có thể đổi trước khi nộp</div>
        </div>

        <div class="qwrap">
          <div id="qimgs" class="qimage"></div>
          <div class="qbody">
            <div id="qtext" class="qtext"></div>
            <div id="answers" class="answers"></div>
          </div>
        </div>

        <div class="exam-foot">
          <button id="btnPrev" type="button" class="btn btn-nav">&laquo; Câu trước</button>
          <button id="btnNext" type="button" class="btn btn-nav">Câu sau &raquo;</button>
        </div>
      </div>

      <!-- Mobile controls: chỉ hiện trên màn hình nhỏ -->
      <div class="mobile-controls" id="mobileControls" style="display:none">
        <label for="selHangM">Hạng</label>
        <select id="selHangM"></select>
        <button id="btnStartM" type="button" class="btn btn-start">Bắt đầu</button>
      </div>

      {{-- Result --}}
      <div id="panelResult" style="display:none">
        <div id="resTitle" class="result"></div>
        <p id="resDetail" class="muted"></p>

        {{-- BẢNG XEM LẠI (thay cho danh sách câu sai) --}}
        <div id="reviewMatrix" class="card" style="margin-top:12px">
          <div class="row" style="justify-content:space-between;align-items:center">
            <h4 style="margin:0">Xem lại bài thi</h4>
            <small class="muted">Nhấp vào một dòng để nhảy đến câu tương ứng.</small>
          </div>
          <div class="tablewrap">
            <table class="rev-table" id="revTbl"></table>
          </div>
        </div>
      </div>
    </main>
  </div>
@endsection

@push('scripts')
  {{-- JS riêng của trang thi --}}
  <script src="{{ asset('js/thi-thu.js') }}?v={{ filemtime(public_path('js/thi-thu.js')) }}" defer></script>
@endpush
