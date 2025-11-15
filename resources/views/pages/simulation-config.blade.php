@extends('layouts.app')
@section('title','Cấu hình điểm trừ mô phỏng')

@push('styles')
<style>
  .config-page {
    margin: 0;
    padding: 20px;
    min-height: 100vh;
    background: #f0f0f0;
  }

  .config-header {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    padding: 16px 24px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .config-header h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
  }

  .config-header p {
    margin: 8px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
  }

  .config-layout {
    display: grid;
    grid-template-columns: 280px 1fr 400px;
    gap: 20px;
    height: calc(100vh - 200px);
  }

  /* Sidebar trái - Danh sách video */
  .config-sidebar-left {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow-y: auto;
    padding: 16px;
  }

  .config-sidebar-title {
    font-weight: 600;
    font-size: 16px;
    color: #1f2937;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
  }

  .config-video-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    margin: 4px 0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    color: #4b5563;
    font-size: 14px;
    text-decoration: none;
  }

  .config-video-item:hover {
    background: #f3f4f6;
    color: #059669;
  }

  .config-video-item.active {
    background: #d1fae5;
    color: #059669;
    font-weight: 600;
  }

  .config-video-item.active .config-video-radio {
    border-color: #059669;
  }

  .config-video-item.active .config-video-radio::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 10px;
    background: #059669;
    border-radius: 50%;
  }

  .config-video-radio {
    width: 18px;
    height: 18px;
    border: 2px solid #9ca3af;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
  }

  /* Video area */
  .config-video-area {
    background: #000;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .config-video-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    min-height: 0;
  }

  .config-video-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  .config-video-controls {
    background: #1f2937;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .config-control-btn {
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

  .config-control-btn:hover {
    background: #4b5563;
    transform: scale(1.05);
  }

  .config-progress-container {
    flex: 1;
    position: relative;
    height: 8px;
    background: #374151;
    border-radius: 4px;
    overflow: visible;
    cursor: pointer;
  }

  .config-progress-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    display: flex;
  }

  .config-progress-segment {
    height: 100%;
    transition: opacity 0.2s;
  }

  .config-progress-segment.diem5 { background: #22c55e; }
  .config-progress-segment.diem4 { background: #84cc16; }
  .config-progress-segment.diem3 { background: #fbbf24; }
  .config-progress-segment.diem2 { background: #f97316; }
  .config-progress-segment.diem1 { background: #ef4444; }
  .config-progress-segment.normal { background: #4b5563; }

  .config-progress-cursor {
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

  .config-progress-marker {
    position: absolute;
    top: -4px;
    width: 4px;
    height: 16px;
    background: #fff;
    border-radius: 2px;
    z-index: 15;
    cursor: pointer;
    box-shadow: 0 0 4px rgba(255,255,255,0.8);
  }

  .config-progress-marker:hover {
    background: #fbbf24;
    transform: scale(1.2);
  }

  .config-progress-time {
    color: #fff;
    font-size: 13px;
    min-width: 80px;
    text-align: center;
    font-weight: 500;
  }

  /* Sidebar phải - Form cấu hình */
  .config-sidebar-right {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow-y: auto;
    padding: 20px;
  }

  .config-form-title {
    font-weight: 600;
    font-size: 18px;
    color: #1f2937;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
  }

  .config-form-group {
    margin-bottom: 20px;
  }

  .config-form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
  }

  .config-form-label .config-label-desc {
    font-weight: 400;
    color: #6b7280;
    font-size: 12px;
    display: block;
    margin-top: 4px;
  }

  .config-form-input {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
  }

  .config-form-input:focus {
    outline: none;
    border-color: #059669;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
  }

  .config-form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
  }

  .config-btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
  }

  .config-btn-primary {
    background: #059669;
    color: #fff;
  }

  .config-btn-primary:hover {
    background: #047857;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(5, 150, 105, 0.3);
  }

  .config-btn-secondary {
    background: #e5e7eb;
    color: #374151;
  }

  .config-btn-secondary:hover {
    background: #d1d5db;
  }

  .config-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .config-help-box {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #166534;
    line-height: 1.6;
  }

  .config-help-box strong {
    display: block;
    margin-bottom: 4px;
  }

  .config-status {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 14px;
    font-weight: 500;
  }

  .config-status.success {
    background: #d1fae5;
    color: #166534;
    border: 1px solid #bbf7d0;
  }

  .config-status.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
  }

  .config-status.info {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
  }

  @media (max-width: 1400px) {
    .config-layout {
      grid-template-columns: 250px 1fr 350px;
    }
  }

  @media (max-width: 1024px) {
    .config-layout {
      grid-template-columns: 1fr;
      height: auto;
    }

    .config-sidebar-left,
    .config-sidebar-right {
      height: auto;
      max-height: 400px;
    }
  }
</style>
@endpush

@section('content')
<div class="config-page">
  <div class="config-header">
    <h1>⚙️ Cấu hình điểm trừ mô phỏng</h1>
    <p>Xem video và đánh dấu các mốc thời gian điểm trừ cho từng tình huống</p>
  </div>

  <div class="config-layout">
    {{-- Sidebar trái - Danh sách video --}}
    <aside class="config-sidebar-left">
      <div class="config-sidebar-title">Danh sách video</div>
      @foreach($allVideos ?? [] as $video)
        <a 
          href="{{ route('simulation.config', ['v' => $video->id]) }}"
          class="config-video-item {{ ($mainVideo && $video->id == $mainVideo->id) ? 'active' : '' }}"
        >
          <div class="config-video-radio"></div>
          <span>TH {{ $video->stt ?? $video->id }}: {{ $video->video }}</span>
        </a>
      @endforeach
    </aside>

    {{-- Video area --}}
    <main class="config-video-area">
      @if($mainVideo)
        <div class="config-video-wrapper">
          <video 
            id="configVideo" 
            controls
            preload="metadata"
            data-video-id="{{ $mainVideo->id }}"
          >
            <source src="{{ asset('videos/' . $mainVideo->video) }}" type="video/mp4">
            Trình duyệt của bạn không hỗ trợ video.
          </video>
        </div>

        <div class="config-video-controls">
          <button class="config-control-btn" id="btnPlayPause" title="Phát/Tạm dừng">▶</button>
          <button class="config-control-btn" id="btnRestart" title="Phát lại">↻</button>
          
          <div class="config-progress-container" id="progressContainer">
            <div class="config-progress-bar" id="progressBar"></div>
            <div class="config-progress-cursor" id="progressCursor"></div>
          </div>

          <div class="config-progress-time">
            <span id="currentTime">00:00</span> / <span id="totalTime">00:00</span>
          </div>
        </div>
      @else
        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#fff;">
          <p>Chưa có video mô phỏng nào</p>
        </div>
      @endif
    </main>

    {{-- Sidebar phải - Form cấu hình --}}
    <aside class="config-sidebar-right">
      @if($mainVideo)
        <div class="config-form-title">Cấu hình điểm trừ</div>
        
        <div id="statusMessage"></div>

        <div class="config-help-box">
          <strong>💡 Hướng dẫn:</strong>
          <ul style="margin: 8px 0 0 0; padding-left: 20px;">
            <li>Phát video và tìm các mốc thời gian cần đánh dấu</li>
            <li>Click vào progress bar hoặc nhấn phím số (1-5) để đánh dấu thời gian</li>
            <li>Nhập thời gian (giây) vào các ô bên dưới</li>
            <li>Nhấn "Lưu cấu hình" để lưu vào database</li>
          </ul>
        </div>

        <form id="configForm">
          <input type="hidden" name="video_id" value="{{ $mainVideo->id }}">

          <div class="config-form-group">
            <label class="config-form-label">
              Điểm 5 (Xanh lá) - Bắt đầu
              <span class="config-label-desc">Thời điểm bắt đầu phát hiện được (không mất điểm)</span>
            </label>
            <div style="display: flex; gap: 8px;">
              <input 
                type="number" 
                name="diem5" 
                class="config-form-input" 
                value="{{ $mainVideo->diem5 }}"
                min="0"
                step="0.001"
                placeholder="Giây (VD: 12.345)"
              >
              <button type="button" class="config-control-btn" data-point="diem5" style="flex-shrink: 0;" title="Đánh dấu thời gian hiện tại">📍</button>
            </div>
          </div>

          <div class="config-form-group">
            <label class="config-form-label">
              Điểm 4 (Vàng xanh) - Trừ 1 điểm
              <span class="config-label-desc">Bắt đầu trừ 1 điểm</span>
            </label>
            <div style="display: flex; gap: 8px;">
              <input 
                type="number" 
                name="diem4" 
                class="config-form-input" 
                value="{{ $mainVideo->diem4 }}"
                min="0"
                step="0.001"
                placeholder="Giây (VD: 12.345)"
              >
              <button type="button" class="config-control-btn" data-point="diem4" style="flex-shrink: 0;" title="Đánh dấu thời gian hiện tại">📍</button>
            </div>
          </div>

          <div class="config-form-group">
            <label class="config-form-label">
              Điểm 3 (Vàng) - Trừ 2 điểm
              <span class="config-label-desc">Bắt đầu trừ 2 điểm</span>
            </label>
            <div style="display: flex; gap: 8px;">
              <input 
                type="number" 
                name="diem3" 
                class="config-form-input" 
                value="{{ $mainVideo->diem3 }}"
                min="0"
                step="0.001"
                placeholder="Giây (VD: 12.345)"
              >
              <button type="button" class="config-control-btn" data-point="diem3" style="flex-shrink: 0;" title="Đánh dấu thời gian hiện tại">📍</button>
            </div>
          </div>

          <div class="config-form-group">
            <label class="config-form-label">
              Điểm 2 (Cam) - Trừ 3 điểm
              <span class="config-label-desc">Bắt đầu trừ 3 điểm</span>
            </label>
            <div style="display: flex; gap: 8px;">
              <input 
                type="number" 
                name="diem2" 
                class="config-form-input" 
                value="{{ $mainVideo->diem2 }}"
                min="0"
                step="0.001"
                placeholder="Giây (VD: 12.345)"
              >
              <button type="button" class="config-control-btn" data-point="diem2" style="flex-shrink: 0;" title="Đánh dấu thời gian hiện tại">📍</button>
            </div>
          </div>

          <div class="config-form-group">
            <label class="config-form-label">
              Điểm 1 (Đỏ) - Trừ 4 điểm (Bắt đầu)
              <span class="config-label-desc">Bắt đầu trừ 4 điểm</span>
            </label>
            <div style="display: flex; gap: 8px;">
              <input 
                type="number" 
                name="diem1" 
                class="config-form-input" 
                value="{{ $mainVideo->diem1 }}"
                min="0"
                step="0.001"
                placeholder="Giây (VD: 12.345)"
              >
              <button type="button" class="config-control-btn" data-point="diem1" style="flex-shrink: 0;" title="Đánh dấu thời gian hiện tại">📍</button>
            </div>
          </div>

          <div class="config-form-group">
            <label class="config-form-label">
              Điểm 1 (Đỏ) - Kết thúc
              <span class="config-label-desc">Kết thúc khoảng trừ 4 điểm (sau đó mất 5 điểm)</span>
            </label>
            <div style="display: flex; gap: 8px;">
              <input 
                type="number" 
                name="diem1end" 
                class="config-form-input" 
                value="{{ $mainVideo->diem1end }}"
                min="0"
                step="0.001"
                placeholder="Giây (VD: 12.345)"
              >
              <button type="button" class="config-control-btn" data-point="diem1end" style="flex-shrink: 0;" title="Đánh dấu thời gian hiện tại">📍</button>
            </div>
          </div>

          <div class="config-form-actions">
            <button type="submit" class="config-btn config-btn-primary" id="btnSave">
              💾 Lưu cấu hình
            </button>
            <button type="button" class="config-btn config-btn-secondary" id="btnReset">
              🔄 Reset
            </button>
          </div>
        </form>
      @else
        <p>Vui lòng chọn một video để cấu hình</p>
      @endif
    </aside>
  </div>
</div>

@push('scripts')
<script>
(function() {
  const video = document.getElementById('configVideo');
  if (!video) return;

  const progressBar = document.getElementById('progressBar');
  const progressCursor = document.getElementById('progressCursor');
  const progressContainer = document.getElementById('progressContainer');
  const currentTimeEl = document.getElementById('currentTime');
  const totalTimeEl = document.getElementById('totalTime');
  const btnPlayPause = document.getElementById('btnPlayPause');
  const btnRestart = document.getElementById('btnRestart');
  const configForm = document.getElementById('configForm');
  const statusMessage = document.getElementById('statusMessage');
  const btnSave = document.getElementById('btnSave');

  let totalDuration = 0;
  const markers = {};

  // Load metadata
  video.addEventListener('loadedmetadata', function() {
    totalDuration = video.duration;
    totalTimeEl.textContent = formatTime(totalDuration);
    buildProgressBar();
  });

  // Update time
  video.addEventListener('timeupdate', function() {
    const current = video.currentTime;
    currentTimeEl.textContent = formatTime(current);
    
    if (totalDuration > 0) {
      const percent = (current / totalDuration) * 100;
      progressCursor.style.left = percent + '%';
    }
  });

  // Build progress bar
  function buildProgressBar() {
    if (totalDuration === 0) return;
    
    progressBar.innerHTML = '';
    
    const diem5 = parseFloat(document.querySelector('[name="diem5"]').value) || 0;
    const diem4 = parseFloat(document.querySelector('[name="diem4"]').value) || 0;
    const diem3 = parseFloat(document.querySelector('[name="diem3"]').value) || 0;
    const diem2 = parseFloat(document.querySelector('[name="diem2"]').value) || 0;
    const diem1 = parseFloat(document.querySelector('[name="diem1"]').value) || 0;
    const diem1end = parseFloat(document.querySelector('[name="diem1end"]').value) || 0;

    const milestones = [
      { time: 0, type: 'normal' },
      { time: diem5, type: 'diem5-start' },
      { time: diem4, type: 'diem4-start' },
      { time: diem3, type: 'diem3-start' },
      { time: diem2, type: 'diem2-start' },
      { time: diem1, type: 'diem1-start' },
      { time: diem1end, type: 'normal' },
      { time: totalDuration, type: 'normal' }
    ].filter(m => m.time > 0 && m.time < totalDuration);

    milestones.sort((a, b) => a.time - b.time);
    const uniqueMilestones = [];
    let prevTime = -1;
    milestones.forEach(m => {
      if (m.time !== prevTime) {
        uniqueMilestones.push(m);
        prevTime = m.time;
      }
    });

    uniqueMilestones.unshift({ time: 0, type: 'normal' });
    uniqueMilestones.push({ time: totalDuration, type: 'normal' });

    for (let i = 0; i < uniqueMilestones.length - 1; i++) {
      const start = uniqueMilestones[i].time;
      const end = uniqueMilestones[i + 1].time;
      const width = ((end - start) / totalDuration) * 100;
      
      if (width > 0) {
        const segment = document.createElement('div');
        let segmentType = 'normal';
        if (start >= diem1 && end <= diem1end) {
          segmentType = 'diem1';
        } else if (start >= diem2 && (diem1 === 0 || end <= diem1)) {
          segmentType = 'diem2';
        } else if (start >= diem3 && (diem2 === 0 || end <= diem2)) {
          segmentType = 'diem3';
        } else if (start >= diem4 && (diem3 === 0 || end <= diem3)) {
          segmentType = 'diem4';
        } else if (start >= diem5 && (diem4 === 0 || end <= diem4)) {
          segmentType = 'diem5';
        }
        
        segment.className = `config-progress-segment ${segmentType}`;
        segment.style.width = width + '%';
        progressBar.appendChild(segment);
      }
    }

    // Add markers
    [diem5, diem4, diem3, diem2, diem1, diem1end].forEach((time, index) => {
      if (time > 0 && time < totalDuration) {
        const marker = document.createElement('div');
        marker.className = 'config-progress-marker';
        marker.style.left = ((time / totalDuration) * 100) + '%';
        marker.title = `Mốc ${index + 1}: ${formatTime(time)}`;
        progressBar.appendChild(marker);
      }
    });
  }

  // Click vào progress bar để seek
  progressContainer.addEventListener('click', function(e) {
    const rect = progressContainer.getBoundingClientRect();
    const percent = (e.clientX - rect.left) / rect.width;
    video.currentTime = percent * totalDuration;
  });

  // Hàm làm tròn đến 3 chữ số thập phân (phù hợp với DECIMAL(6,3))
  function roundTo3Decimals(value) {
    return Math.round(value * 1000) / 1000;
  }

  // Click vào marker để đánh dấu
  document.querySelectorAll('[data-point]').forEach(btn => {
    btn.addEventListener('click', function() {
      const point = this.dataset.point;
      const currentTime = roundTo3Decimals(video.currentTime);
      document.querySelector(`[name="${point}"]`).value = currentTime;
      buildProgressBar();
      showStatus('Đã đánh dấu ' + point + ' tại ' + currentTime.toFixed(3) + 's', 'success');
    });
  });

  // Nhấn phím số để đánh dấu
  document.addEventListener('keydown', function(e) {
    if (e.target.tagName === 'INPUT') return;
    
    const keyMap = {
      'Digit1': 'diem5',
      'Digit2': 'diem4',
      'Digit3': 'diem3',
      'Digit4': 'diem2',
      'Digit5': 'diem1',
      'Digit6': 'diem1end'
    };

    if (keyMap[e.code]) {
      e.preventDefault();
      const point = keyMap[e.code];
      const currentTime = roundTo3Decimals(video.currentTime);
      document.querySelector(`[name="${point}"]`).value = currentTime;
      buildProgressBar();
      showStatus('Đã đánh dấu ' + point + ' tại ' + currentTime.toFixed(3) + 's (Phím ' + e.code.replace('Digit', '') + ')', 'success');
    }
  });

  // Listen to input changes to rebuild progress bar
  document.querySelectorAll('.config-form-input').forEach(input => {
    input.addEventListener('input', function() {
      buildProgressBar();
    });
  });

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
    video.play();
    btnPlayPause.textContent = '⏸';
  });

  // Form submit
  configForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    btnSave.disabled = true;
    btnSave.textContent = '⏳ Đang lưu...';

    const formData = new FormData(configForm);
    const data = Object.fromEntries(formData);
    
    // Convert to integers
    Object.keys(data).forEach(key => {
      if (key !== 'video_id') {
        data[key] = parseInt(data[key]) || 0;
      }
    });

    try {
      const response = await fetch('{{ route("api.simulation.save-points") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (result.success) {
        showStatus('✅ Đã lưu cấu hình thành công!', 'success');
        buildProgressBar();
      } else {
        showStatus('❌ Lỗi: ' + (result.message || 'Không thể lưu'), 'error');
      }
    } catch (error) {
      showStatus('❌ Lỗi kết nối: ' + error.message, 'error');
    } finally {
      btnSave.disabled = false;
      btnSave.textContent = '💾 Lưu cấu hình';
    }
  });

  // Reset button
  document.getElementById('btnReset').addEventListener('click', function() {
    if (confirm('Bạn có chắc muốn reset tất cả các giá trị về 0?')) {
      document.querySelectorAll('.config-form-input').forEach(input => {
        input.value = '0';
      });
      buildProgressBar();
      showStatus('Đã reset tất cả giá trị', 'info');
    }
  });

  // Show status message
  function showStatus(message, type = 'info') {
    statusMessage.innerHTML = `<div class="config-status ${type}">${message}</div>`;
    setTimeout(() => {
      statusMessage.innerHTML = '';
    }, 5000);
  }

  // Format time
  function formatTime(seconds) {
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
  }

  // Initial build
  if (totalDuration > 0) {
    buildProgressBar();
  }
})();
</script>
@endpush
@endsection

