@extends('layouts.app')
@section('title','Mô phỏng lý thuyết lái xe')

@push('styles')
<style>
  /* Reset container cho trang mô phỏng */
  .simulation-page {
    margin: 0;
    padding: 0;
    width: 100%;
    min-height: 100vh;
    background: #f0f0f0;
  }

  .simulation-page .container {
    max-width: 100%;
    padding: 0;
  }

  /* Header giống phần mềm */
  .sim-header {
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: #fff;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .sim-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .sim-logo {
    width: 50px;
    height: 50px;
    background: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #1e40af;
  }

  .sim-header-title {
    display: flex;
    flex-direction: column;
  }

  .sim-header-title-main {
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
  }

  .sim-header-title-sub {
    font-size: 14px;
    opacity: 0.95;
    margin-top: 2px;
  }

  .sim-header-right {
    display: flex;
    gap: 20px;
  }

  .sim-header-link {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    padding: 6px 12px;
    border-radius: 6px;
    transition: background 0.2s;
  }

  .sim-header-link:hover {
    background: rgba(255,255,255,0.15);
  }

  /* Main content layout - 3 cột */
  .sim-main-layout {
    display: grid;
    grid-template-columns: 280px 1fr 320px;
    gap: 0;
    height: calc(100vh - 60px);
    overflow: hidden;
  }

  /* Cột trái - Danh sách tình huống */
  .sim-sidebar-left {
    background: #fff;
    border-right: 1px solid #e5e7eb;
    overflow-y: auto;
    height: 100%;
  }

  .sim-sidebar-title {
    padding: 16px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    font-size: 16px;
    color: #1f2937;
  }

  .sim-chapter {
    padding: 12px 16px;
  }

  .sim-chapter-header {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .sim-chapter-header:hover {
    color: #1e40af;
  }

  .sim-chapter-content {
    margin-left: 16px;
  }

  .sim-situation-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    margin: 4px 0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    color: #4b5563;
    font-size: 14px;
  }

  .sim-situation-item:hover {
    background: #f3f4f6;
    color: #1e40af;
  }

  .sim-situation-item.active {
    background: #dbeafe;
    color: #1e40af;
    font-weight: 600;
  }

  .sim-situation-radio {
    width: 18px;
    height: 18px;
    border: 2px solid #9ca3af;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
  }

  .sim-situation-item.active .sim-situation-radio {
    border-color: #3b82f6;
  }

  .sim-situation-item.active .sim-situation-radio::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 10px;
    background: #3b82f6;
    border-radius: 50%;
  }

  /* Cột giữa - Video player */
  .sim-video-area {
    background: #000;
    display: flex;
    flex-direction: column;
    position: relative;
    height: 100%;
  }

  .sim-video-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: #000;
    min-height: 0; /* Quan trọng cho flex */
  }

  .sim-video-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: contain;
    max-width: 100%;
    max-height: 100%;
  }

  /* Video controls */
  .sim-video-controls {
    background: #1f2937;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .sim-control-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: #374151;
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.2s;
  }

  .sim-control-btn:hover {
    background: #4b5563;
    transform: scale(1.05);
  }

  .sim-control-btn.active {
    background: #3b82f6;
  }

  /* Progress bar với màu sắc */
  .sim-progress-container {
    flex: 1;
    position: relative;
    height: 8px;
    background: #374151;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
  }

  .sim-progress-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    display: flex;
  }

  .sim-progress-segment {
    height: 100%;
    transition: opacity 0.2s;
  }

  .sim-progress-segment.diem5 {
    background: #22c55e; /* Xanh lá */
  }

  .sim-progress-segment.diem4 {
    background: #84cc16; /* Vàng xanh */
  }

  .sim-progress-segment.diem3 {
    background: #fbbf24; /* Vàng */
  }

  .sim-progress-segment.diem2 {
    background: #f97316; /* Cam */
  }

  .sim-progress-segment.diem1 {
    background: #ef4444; /* Đỏ */
  }

  .sim-progress-segment.normal {
    background: #4b5563; /* Xám */
  }

  .sim-progress-cursor {
    position: absolute;
    top: 0;
    width: 3px;
    height: 100%;
    background: #fff;
    box-shadow: 0 0 4px rgba(255,255,255,0.8);
    z-index: 10;
    pointer-events: none;
    transition: left 0.1s linear;
  }

  .sim-progress-time {
    color: #fff;
    font-size: 13px;
    min-width: 80px;
    text-align: center;
    font-weight: 500;
  }

  /* Cột phải - Kết quả */
  .sim-sidebar-right {
    background: #fff;
    border-left: 1px solid #e5e7eb;
    overflow-y: auto;
    height: 100%;
  }

  .sim-results-title {
    padding: 16px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    font-size: 16px;
    color: #1f2937;
  }

  .sim-results-content {
    padding: 20px;
  }

  .sim-result-item {
    margin-bottom: 16px;
  }

  .sim-result-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
  }

  .sim-result-value {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
  }

  .sim-result-value.score {
    color: #059669;
    font-size: 20px;
  }

  .sim-situation-description {
    margin-top: 20px;
  }

  .sim-description-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
  }

  .sim-description-text {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px;
    font-size: 14px;
    color: #4b5563;
    line-height: 1.6;
    min-height: 100px;
  }

  /* Bottom instruction bar */
  .sim-instruction-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #dc2626;
    color: #fff;
    padding: 12px 20px;
    text-align: center;
    font-size: 15px;
    font-weight: 500;
    z-index: 100;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.2);
  }

  /* Responsive cho Desktop lớn */
  @media (min-width: 1920px) {
    .sim-main-layout {
      grid-template-columns: 320px 1fr 380px;
    }
    .sim-sidebar-left {
      font-size: 15px;
    }
    .sim-video-controls {
      padding: 16px 24px;
    }
    .sim-control-btn {
      width: 48px;
      height: 48px;
      font-size: 20px;
    }
  }

  /* Responsive cho Desktop vừa */
  @media (max-width: 1400px) and (min-width: 1025px) {
    .sim-main-layout {
      grid-template-columns: 250px 1fr 280px;
    }
  }

  /* Responsive cho Tablet */
  @media (max-width: 1024px) and (min-width: 769px) {
    .sim-main-layout {
      grid-template-columns: 220px 1fr 260px;
      height: calc(100vh - 60px);
    }
    
    .sim-header-title-main {
      font-size: 16px;
    }
    
    .sim-header-title-sub {
      font-size: 12px;
    }
    
    .sim-sidebar-left,
    .sim-sidebar-right {
      font-size: 13px;
    }
    
    .sim-situation-item {
      padding: 6px 10px;
      font-size: 13px;
    }
    
    .sim-control-btn {
      width: 36px;
      height: 36px;
      font-size: 16px;
    }
    
    .sim-progress-container {
      height: 6px;
    }
    
    .sim-instruction-bar {
      font-size: 13px;
      padding: 10px 16px;
    }
  }

  /* Responsive cho Mobile */
  @media (max-width: 768px) {
    .simulation-page {
      height: 100vh;
      overflow: hidden;
    }

    .sim-header {
      padding: 10px 12px;
      flex-wrap: wrap;
    }

    .sim-header-left {
      flex: 1;
      min-width: 0;
    }

    .sim-logo {
      width: 40px;
      height: 40px;
      font-size: 12px;
    }

    .sim-header-title-main {
      font-size: 14px;
      line-height: 1.2;
    }

    .sim-header-title-sub {
      font-size: 11px;
      display: none; /* Ẩn trên mobile để tiết kiệm không gian */
    }

    .sim-header-right {
      gap: 12px;
    }

    .sim-header-link {
      font-size: 12px;
      padding: 5px 10px;
    }

    .sim-main-layout {
      grid-template-columns: 1fr;
      height: calc(100vh - 60px - 40px); /* Trừ header và instruction bar */
      display: flex;
      flex-direction: column;
    }

    /* Sidebar trái - Ẩn mặc định, có thể toggle */
    .sim-sidebar-left {
      display: none;
      position: fixed;
      top: 60px;
      left: 0;
      width: 280px;
      height: calc(100vh - 100px);
      z-index: 200;
      box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    }

    .sim-sidebar-left.show {
      display: block;
    }

    /* Sidebar phải - Ẩn mặc định, có thể toggle */
    .sim-sidebar-right {
      display: none;
      position: fixed;
      top: 60px;
      right: 0;
      width: 280px;
      height: calc(100vh - 100px);
      z-index: 200;
      box-shadow: -2px 0 10px rgba(0,0,0,0.2);
    }

    .sim-sidebar-right.show {
      display: block;
    }

    /* Video area chiếm toàn bộ */
    .sim-video-area {
      flex: 1;
      min-height: 0;
    }

    .sim-video-wrapper {
      height: 100%;
    }

    /* Video controls cho mobile */
    .sim-video-controls {
      padding: 10px 12px;
      flex-wrap: wrap;
      gap: 8px;
      background: #1a1f2e;
    }

    .sim-control-btn {
      width: 40px;
      height: 40px;
      font-size: 18px;
      flex-shrink: 0;
    }

    .sim-progress-container {
      order: 3;
      width: 100%;
      height: 6px;
      margin-top: 8px;
    }

    .sim-progress-time {
      order: 2;
      font-size: 12px;
      min-width: 70px;
      flex-shrink: 0;
    }

    /* Floating buttons cho mobile */
    .sim-mobile-toggle {
      position: fixed;
      z-index: 150;
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: #3b82f6;
      color: #fff;
      border: none;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .sim-mobile-toggle:hover {
      background: #2563eb;
      transform: scale(1.1);
    }

    .sim-mobile-toggle-left {
      top: 70px;
      left: 12px;
    }

    .sim-mobile-toggle-right {
      top: 70px;
      right: 12px;
    }

    .sim-mobile-toggle.active {
      background: #10b981;
    }

    /* Sidebar content trên mobile */
    .sim-sidebar-title,
    .sim-results-title {
      padding: 12px;
      font-size: 14px;
    }

    .sim-chapter {
      padding: 10px 12px;
    }

    .sim-chapter-header {
      font-size: 14px;
    }

    .sim-situation-item {
      padding: 8px 10px;
      font-size: 13px;
    }

    .sim-results-content {
      padding: 16px;
    }

    .sim-result-item {
      margin-bottom: 12px;
    }

    .sim-result-value {
      font-size: 14px;
    }

    .sim-result-value.score {
      font-size: 18px;
    }

    .sim-description-label {
      font-size: 13px;
    }

    .sim-description-text {
      font-size: 13px;
      padding: 10px;
      min-height: 80px;
    }

    /* Instruction bar cho mobile */
    .sim-instruction-bar {
      font-size: 12px;
      padding: 10px 12px;
      line-height: 1.4;
    }

    /* Overlay khi sidebar mở */
    .sim-sidebar-overlay {
      display: none;
      position: fixed;
      top: 60px;
      left: 0;
      right: 0;
      bottom: 40px;
      background: rgba(0,0,0,0.5);
      z-index: 199;
    }

    .sim-sidebar-overlay.show {
      display: block;
    }
  }

  /* Responsive cho Mobile nhỏ */
  @media (max-width: 480px) {
    .sim-header {
      padding: 8px 10px;
    }

    .sim-logo {
      width: 36px;
      height: 36px;
    }

    .sim-header-title-main {
      font-size: 13px;
    }

    .sim-main-layout {
      height: calc(100vh - 56px - 36px);
    }

    .sim-sidebar-left,
    .sim-sidebar-right {
      width: 85vw;
      max-width: 300px;
    }

    .sim-mobile-toggle {
      width: 44px;
      height: 44px;
      font-size: 18px;
    }

    .sim-mobile-toggle-left {
      left: 8px;
      top: 66px;
    }

    .sim-mobile-toggle-right {
      right: 8px;
      top: 66px;
    }

    .sim-video-controls {
      padding: 8px 10px;
    }

    .sim-control-btn {
      width: 36px;
      height: 36px;
      font-size: 16px;
    }

    .sim-progress-time {
      font-size: 11px;
      min-width: 60px;
    }

    .sim-instruction-bar {
      font-size: 11px;
      padding: 8px 10px;
    }
  }
</style>
@endpush

@section('content')
<div class="simulation-page">
  {{-- Header --}}
  <div class="sim-header">
    <div class="sim-header-left">
      <div class="sim-logo">ĐBVN</div>
      <div class="sim-header-title">
        <div class="sim-header-title-main">CỤC ĐƯỜNG BỘ VIỆT NAM</div>
        <div class="sim-header-title-sub">PHẦN MỀM ÔN TẬP MÔ PHỎNG CÁC TÌNH HUỐNG GIAO THÔNG</div>
      </div>
    </div>
    <div class="sim-header-right">
      <a href="#" class="sim-header-link">Thông tin</a>
      <a href="#" class="sim-header-link">Trợ giúp</a>
    </div>
  </div>

  {{-- Mobile toggle buttons --}}
  <button class="sim-mobile-toggle sim-mobile-toggle-left" id="btnToggleLeft" aria-label="Mở danh sách tình huống">
    ☰
  </button>
  <button class="sim-mobile-toggle sim-mobile-toggle-right" id="btnToggleRight" aria-label="Mở kết quả">
    📊
  </button>
  <div class="sim-sidebar-overlay" id="sidebarOverlay"></div>

  {{-- Main Layout - 3 cột --}}
  <div class="sim-main-layout">
    {{-- Cột trái - Danh sách tình huống --}}
    <aside class="sim-sidebar-left" id="sidebarLeft">
      <div class="sim-sidebar-title">Ôn tập</div>
      <div class="sim-chapter">
        <div class="sim-chapter-header">
          <span>Nội dung</span>
        </div>
        <div class="sim-chapter-content">
          <div class="sim-chapter-header" style="margin-top: 12px;">Chương 1</div>
          @foreach($allVideos ?? [] as $video)
            <a 
              href="{{ route('simulation', ['v' => $video->id]) }}"
              class="sim-situation-item {{ ($mainVideo && $video->id == $mainVideo->id) ? 'active' : '' }}"
              data-video-id="{{ $video->id }}"
            >
              <div class="sim-situation-radio"></div>
              <span>TH {{ $video->stt ?? $video->id }}</span>
            </a>
          @endforeach
        </div>
      </div>
    </aside>

    {{-- Cột giữa - Video player --}}
    <main class="sim-video-area">
      @if($mainVideo)
        <div class="sim-video-wrapper">
          <video 
            id="mainVideo" 
            controls
            preload="metadata"
            data-video-id="{{ $mainVideo->id }}"
            data-diem5="{{ $mainVideo->diem5 }}"
            data-diem4="{{ $mainVideo->diem4 }}"
            data-diem3="{{ $mainVideo->diem3 }}"
            data-diem2="{{ $mainVideo->diem2 }}"
            data-diem1="{{ $mainVideo->diem1 }}"
            data-diem1end="{{ $mainVideo->diem1end }}"
            data-duration="0"
          >
            <source src="{{ asset('videos/' . $mainVideo->video) }}" type="video/mp4">
            Trình duyệt của bạn không hỗ trợ video.
          </video>
        </div>

        {{-- Video controls với progress bar --}}
        <div class="sim-video-controls">
          <button class="sim-control-btn" id="btnPrev" title="Tình huống trước">⏮</button>
          <button class="sim-control-btn" id="btnPlayPause" title="Phát/Tạm dừng">▶</button>
          <button class="sim-control-btn" id="btnRestart" title="Phát lại">↻</button>
          <button class="sim-control-btn" id="btnNext" title="Tình huống tiếp">⏭</button>
          
          <div class="sim-progress-container" id="progressContainer">
            <div class="sim-progress-bar" id="progressBar"></div>
            <div class="sim-progress-cursor" id="progressCursor"></div>
          </div>

          <div class="sim-progress-time">
            <span id="currentTime">00:00</span> / <span id="totalTime">00:00</span>
          </div>
        </div>
      @else
        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#fff;">
          <p>Chưa có video mô phỏng nào</p>
        </div>
      @endif
    </main>

    {{-- Cột phải - Kết quả --}}
    <aside class="sim-sidebar-right" id="sidebarRight">
      <div class="sim-results-title">Kết quả</div>
      <div class="sim-results-content">
        @if($mainVideo)
          <div class="sim-result-item">
            <div class="sim-result-label">Số tình huống:</div>
            <div class="sim-result-value">1</div>
          </div>
          <div class="sim-result-item">
            <div class="sim-result-label">Điểm:</div>
            <div class="sim-result-value score" id="resultScore">-/5</div>
          </div>
          <div class="sim-situation-description">
            <div class="sim-description-label">Tình huống:</div>
            <div class="sim-description-text" id="situationDesc">
              Tình huống {{ $mainVideo->stt ?? $mainVideo->id }} - {{ $mainVideo->video }}
            </div>
          </div>
        @endif
      </div>
    </aside>
  </div>

  {{-- Bottom instruction bar --}}
  <div class="sim-instruction-bar">
    Học viên ấn phím SPACE khi phát hiện tình huống nguy hiểm
  </div>
</div>

@push('scripts')
<script>
(function() {
  const video = document.getElementById('mainVideo');
  if (!video) return;

  const videoId = video.dataset.videoId;
  const progressBar = document.getElementById('progressBar');
  const progressCursor = document.getElementById('progressCursor');
  const progressContainer = document.getElementById('progressContainer');
  const currentTimeEl = document.getElementById('currentTime');
  const totalTimeEl = document.getElementById('totalTime');
  const btnPlayPause = document.getElementById('btnPlayPause');
  const btnRestart = document.getElementById('btnRestart');
  const btnPrev = document.getElementById('btnPrev');
  const btnNext = document.getElementById('btnNext');
  const resultScore = document.getElementById('resultScore');

  // Điểm trừ
  const diem5 = parseInt(video.dataset.diem5) || 0;
  const diem4 = parseInt(video.dataset.diem4) || 0;
  const diem3 = parseInt(video.dataset.diem3) || 0;
  const diem2 = parseInt(video.dataset.diem2) || 0;
  const diem1 = parseInt(video.dataset.diem1) || 0;
  const diem1end = parseInt(video.dataset.diem1end) || 0;

  let totalDuration = 0;
  let currentScore = 5;
  let hasDetected = false;

  // Load metadata để lấy duration
  video.addEventListener('loadedmetadata', function() {
    totalDuration = video.duration;
    video.dataset.duration = totalDuration;
    totalTimeEl.textContent = formatTime(totalDuration);
    buildProgressBar();
  });

  // Update thời gian
  video.addEventListener('timeupdate', function() {
    const current = video.currentTime;
    currentTimeEl.textContent = formatTime(current);
    
    // Update cursor
    if (totalDuration > 0) {
      const percent = (current / totalDuration) * 100;
      progressCursor.style.left = percent + '%';
    }

    // Kiểm tra điểm trừ
    checkScore(current);
  });

  // Build progress bar với màu sắc
  function buildProgressBar() {
    if (totalDuration === 0) return;
    
    progressBar.innerHTML = '';
    
    // Tạo mảng các điểm mốc thời gian (theo thứ tự từ sớm đến muộn)
    const milestones = [];
    
    // Thêm điểm đầu
    milestones.push({ time: 0, type: 'normal' });
    
    // Thêm các điểm mốc (chỉ lấy những điểm > 0 và hợp lệ)
    if (diem5 > 0 && diem5 < totalDuration) {
      milestones.push({ time: diem5, type: 'diem5-start' });
    }
    if (diem4 > 0 && diem4 < totalDuration) {
      milestones.push({ time: diem4, type: 'diem4-start' });
    }
    if (diem3 > 0 && diem3 < totalDuration) {
      milestones.push({ time: diem3, type: 'diem3-start' });
    }
    if (diem2 > 0 && diem2 < totalDuration) {
      milestones.push({ time: diem2, type: 'diem2-start' });
    }
    if (diem1 > 0 && diem1 < totalDuration) {
      milestones.push({ time: diem1, type: 'diem1-start' });
    }
    if (diem1end > 0 && diem1end < totalDuration) {
      milestones.push({ time: diem1end, type: 'normal' });
    }
    
    // Thêm điểm cuối
    milestones.push({ time: totalDuration, type: 'normal' });
    
    // Sắp xếp theo thời gian
    milestones.sort((a, b) => a.time - b.time);
    
    // Loại bỏ các điểm trùng lặp
    const uniqueMilestones = [];
    let prevTime = -1;
    milestones.forEach(m => {
      if (m.time !== prevTime) {
        uniqueMilestones.push(m);
        prevTime = m.time;
      }
    });
    
    // Tạo các đoạn màu
    for (let i = 0; i < uniqueMilestones.length - 1; i++) {
      const start = uniqueMilestones[i].time;
      const end = uniqueMilestones[i + 1].time;
      const width = ((end - start) / totalDuration) * 100;
      
      if (width > 0) {
        const segment = document.createElement('div');
        
        // Xác định màu dựa trên khoảng thời gian
        let segmentType = 'normal';
        if (start >= diem1 && end <= diem1end) {
          segmentType = 'diem1'; // Đỏ
        } else if (start >= diem2 && (diem1 === 0 || end <= diem1)) {
          segmentType = 'diem2'; // Cam
        } else if (start >= diem3 && (diem2 === 0 || end <= diem2)) {
          segmentType = 'diem3'; // Vàng
        } else if (start >= diem4 && (diem3 === 0 || end <= diem3)) {
          segmentType = 'diem4'; // Vàng xanh
        } else if (start >= diem5 && (diem4 === 0 || end <= diem4)) {
          segmentType = 'diem5'; // Xanh lá
        }
        
        segment.className = `sim-progress-segment ${segmentType}`;
        segment.style.width = width + '%';
        segment.setAttribute('data-start', start);
        segment.setAttribute('data-end', end);
        progressBar.appendChild(segment);
      }
    }
    
    // Nếu không có điểm nào, tạo 1 đoạn normal
    if (progressBar.children.length === 0) {
      const segment = document.createElement('div');
      segment.className = 'sim-progress-segment normal';
      segment.style.width = '100%';
      progressBar.appendChild(segment);
    }
    
    console.log('Progress bar đã được build với', progressBar.children.length, 'đoạn màu');
  }

  // Kiểm tra điểm dựa trên thời gian hiện tại (chỉ hiển thị, không thay đổi điểm khi chưa nhấn Space)
  function checkScore(currentTime) {
    // Chỉ hiển thị điểm tiềm năng, không thay đổi điểm thực tế cho đến khi nhấn Space
    // Logic này sẽ chỉ cập nhật sau khi nhấn Space
  }

  // Tính điểm dựa trên thời điểm nhấn Space
  function calculateScore(currentTime) {
    // Logic: Xác định xem thời điểm nhấn Space nằm trong khoảng điểm trừ nào
    // Điểm còn lại = 5 - điểm_trừ
    
    // Kiểm tra các khoảng điểm trừ theo thứ tự từ nghiêm trọng nhất (điểm trừ nhiều nhất) đến ít nhất
    
    // Điểm trừ 4 (còn 1 điểm): khoảng diem1 đến diem1end
    if (diem1 > 0 && diem1end > 0 && currentTime >= diem1 && currentTime <= diem1end) {
      console.log(`Thời điểm ${currentTime}s: Trong khoảng diem1 [${diem1}-${diem1end}], mất 4 điểm -> còn 1 điểm`);
      return 1;
    }
    
    // Điểm trừ 3 (còn 2 điểm): khoảng diem2 đến diem1
    if (diem2 > 0 && currentTime >= diem2) {
      if (diem1 === 0 || currentTime < diem1) {
        console.log(`Thời điểm ${currentTime}s: Trong khoảng diem2 [${diem2}-${diem1 || 'cuối'}], mất 3 điểm -> còn 2 điểm`);
        return 2;
      }
    }
    
    // Điểm trừ 2 (còn 3 điểm): khoảng diem3 đến diem2
    if (diem3 > 0 && currentTime >= diem3) {
      if (diem2 === 0 || currentTime < diem2) {
        console.log(`Thời điểm ${currentTime}s: Trong khoảng diem3 [${diem3}-${diem2 || 'cuối'}], mất 2 điểm -> còn 3 điểm`);
        return 3;
      }
    }
    
    // Điểm trừ 1 (còn 4 điểm): khoảng diem4 đến diem3
    if (diem4 > 0 && currentTime >= diem4) {
      if (diem3 === 0 || currentTime < diem3) {
        console.log(`Thời điểm ${currentTime}s: Trong khoảng diem4 [${diem4}-${diem3 || 'cuối'}], mất 1 điểm -> còn 4 điểm`);
        return 4;
      }
    }
    
    // Điểm trừ 0 (còn 5 điểm): khoảng diem5 đến diem4 HOẶC phát hiện sớm (trước diem5)
    if (diem5 > 0) {
      if (currentTime < diem5) {
        console.log(`Thời điểm ${currentTime}s: Phát hiện sớm (trước ${diem5}s), được 5 điểm`);
        return 5;
      }
      if (diem4 === 0 || currentTime < diem4) {
        console.log(`Thời điểm ${currentTime}s: Trong khoảng diem5 [${diem5}-${diem4 || 'cuối'}], không mất điểm -> còn 5 điểm`);
        return 5;
      }
    }
    
    // Phát hiện muộn (sau diem1end): mất 5 điểm
    if (diem1end > 0 && currentTime > diem1end) {
      console.log(`Thời điểm ${currentTime}s: Phát hiện muộn (sau ${diem1end}s), mất 5 điểm -> còn 0 điểm`);
      return 0;
    }
    
    // Nếu không có điểm nào được cấu hình
    if (diem5 === 0 && diem4 === 0 && diem3 === 0 && diem2 === 0 && diem1 === 0) {
      console.warn(`⚠️ CHƯA CẤU HÌNH ĐIỂM TRỪ! Vui lòng cấu hình các điểm trừ cho video này.`);
      return 5; // Mặc định 5 điểm khi chưa cấu hình
    }
    
    // Trường hợp khác (không rơi vào khoảng nào)
    console.log(`Thời điểm ${currentTime}s: Không xác định được khoảng, mặc định 5 điểm`);
    return 5;
  }

  // Nhấn Space để bắt điểm
  document.addEventListener('keydown', function(e) {
    if (e.code === 'Space') {
      if (video.paused) {
        // Nếu đang pause, cho phép play
        e.preventDefault();
        video.play();
        btnPlayPause.textContent = '⏸';
        return;
      }
      
      if (!hasDetected && !video.paused) {
        e.preventDefault();
        hasDetected = true;
        video.pause();
        btnPlayPause.textContent = '▶';
        
        // Tính điểm dựa trên thời điểm nhấn
        const currentTime = Math.floor(video.currentTime);
        const score = calculateScore(currentTime);

        currentScore = score;
        resultScore.textContent = score + '/5';
        
        // Log để debug
        console.log('=== TÍNH ĐIỂM ===');
        console.log('Thời điểm nhấn Space:', currentTime, 'giây');
        console.log('Điểm trừ đã cấu hình:', {
          diem5: diem5 || 'Chưa cấu hình',
          diem4: diem4 || 'Chưa cấu hình',
          diem3: diem3 || 'Chưa cấu hình',
          diem2: diem2 || 'Chưa cấu hình',
          diem1: diem1 || 'Chưa cấu hình',
          diem1end: diem1end || 'Chưa cấu hình'
        });
        console.log('Điểm tính được:', score);
        
        // Highlight đoạn tương ứng
        highlightSegment(currentTime);
      }
    }
  });

  // Highlight đoạn trên progress bar
  function highlightSegment(time) {
    const segments = progressBar.querySelectorAll('.sim-progress-segment');
    segments.forEach(seg => seg.style.opacity = '0.3');
    
    // Tìm và highlight đoạn chứa thời điểm này
    let accumulated = 0;
    segments.forEach(seg => {
      const width = parseFloat(seg.style.width);
      const startTime = (accumulated / 100) * totalDuration;
      const endTime = ((accumulated + width) / 100) * totalDuration;
      
      if (time >= startTime && time <= endTime) {
        seg.style.opacity = '1';
        seg.style.boxShadow = '0 0 8px rgba(255,255,255,0.6)';
      }
      
      accumulated += width;
    });
  }

  // Video controls
  btnPlayPause.addEventListener('click', function() {
    if (video.paused) {
      video.play();
      this.textContent = '⏸';
    } else {
      video.pause();
      this.textContent = '▶';
    }
  });

  btnRestart.addEventListener('click', function() {
    video.currentTime = 0;
    hasDetected = false;
    currentScore = 5;
    resultScore.textContent = '-/5';
    progressBar.querySelectorAll('.sim-progress-segment').forEach(seg => {
      seg.style.opacity = '1';
      seg.style.boxShadow = 'none';
    });
    video.play();
    btnPlayPause.textContent = '⏸';
  });

  // Click vào progress bar để seek
  progressContainer.addEventListener('click', function(e) {
    const rect = progressContainer.getBoundingClientRect();
    const percent = (e.clientX - rect.left) / rect.width;
    video.currentTime = percent * totalDuration;
  });

  // Navigation
  btnPrev.addEventListener('click', function() {
    const currentItem = document.querySelector('.sim-situation-item.active');
    if (currentItem) {
      const prevItem = currentItem.previousElementSibling;
      if (prevItem && prevItem.classList.contains('sim-situation-item')) {
        const href = prevItem.getAttribute('href');
        if (href) window.location.href = href;
      }
    }
  });

  btnNext.addEventListener('click', function() {
    const currentItem = document.querySelector('.sim-situation-item.active');
    if (currentItem) {
      const nextItem = currentItem.nextElementSibling;
      if (nextItem && nextItem.classList.contains('sim-situation-item')) {
        const href = nextItem.getAttribute('href');
        if (href) window.location.href = href;
      }
    }
  });

  // Format time
  function formatTime(seconds) {
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
  }

  // Auto play khi load
  video.addEventListener('canplay', function() {
    // Không auto play, để người dùng tự điều khiển
  });

  // Mobile sidebar toggle
  const btnToggleLeft = document.getElementById('btnToggleLeft');
  const btnToggleRight = document.getElementById('btnToggleRight');
  const sidebarLeft = document.getElementById('sidebarLeft');
  const sidebarRight = document.getElementById('sidebarRight');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  function toggleSidebar(sidebar, button) {
    if (sidebar && button) {
      sidebar.classList.toggle('show');
      button.classList.toggle('active');
      sidebarOverlay.classList.toggle('show');
    }
  }

  function closeSidebars() {
    sidebarLeft.classList.remove('show');
    sidebarRight.classList.remove('show');
    btnToggleLeft.classList.remove('active');
    btnToggleRight.classList.remove('active');
    sidebarOverlay.classList.remove('show');
  }

  if (btnToggleLeft && sidebarLeft) {
    btnToggleLeft.addEventListener('click', function() {
      // Đóng sidebar phải nếu đang mở
      if (sidebarRight.classList.contains('show')) {
        sidebarRight.classList.remove('show');
        btnToggleRight.classList.remove('active');
      }
      toggleSidebar(sidebarLeft, btnToggleLeft);
    });
  }

  if (btnToggleRight && sidebarRight) {
    btnToggleRight.addEventListener('click', function() {
      // Đóng sidebar trái nếu đang mở
      if (sidebarLeft.classList.contains('show')) {
        sidebarLeft.classList.remove('show');
        btnToggleLeft.classList.remove('active');
      }
      toggleSidebar(sidebarRight, btnToggleRight);
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', closeSidebars);
  }

  // Đóng sidebar khi click vào item
  if (sidebarLeft) {
    sidebarLeft.addEventListener('click', function(e) {
      if (e.target.closest('.sim-situation-item')) {
        // Delay một chút để có thể navigate trước
        setTimeout(closeSidebars, 300);
      }
    });
  }

  // Handle window resize
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      // Đóng sidebar khi resize
      if (window.innerWidth > 768) {
        closeSidebars();
      }
    }, 250);
  });
})();
</script>
@endpush
@endsection
