<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Thi thử theo hạng</title>
    <style>
        body { font-family: system-ui, Arial, sans-serif; margin: 0; background: #f7f7f7; }
        header { background: #0b63b6; color:#fff; padding:12px 16px; }
        .wrap { padding:16px; display:grid; grid-template-columns: 280px 1fr; gap:16px; }
        .card { background:#fff; border-radius:12px; padding:12px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
        .row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .select, .btn { padding:10px 12px; border-radius:8px; border:1px solid #ddd; background:#fafafa; }
        .btn.primary { background:#0b63b6; color:#fff; border-color:#0b63b6; cursor:pointer; }
        .btn:disabled { opacity:.6; cursor:not-allowed; }
        .timer { font-weight:700; font-size:18px; }
        .grid { display:grid; grid-template-columns:repeat(5,1fr); gap:8px; max-height:60vh; overflow:auto; }
        .num { border:1px solid #ddd; border-radius:8px; padding:8px; text-align:center; cursor:pointer; }
        .num.active { background:#0b63b6; color:#fff; border-color:#0b63b6; }
        .num.liet { border-color:#ff7b7b; }
        .qtext { font-size:18px; margin-bottom:8px; }
        .answers { display:grid; gap:8px; }
        .ans { border:1px solid #ddd; border-radius:8px; padding:10px; cursor:pointer; background:#fff; }
        .ans.selected { outline:2px solid #0b63b6; }
        .images { display:flex; gap:8px; flex-wrap:wrap; margin:10px 0; }
        .images img { max-height:200px; border-radius:8px; border:1px solid #eee; }
        .result.pass { color:#18984a; font-weight:700; }
        .result.fail { color:#d93025; font-weight:700; }
        .muted { color:#666; font-size:14px; }
    </style>
</head>
<body>
<header>
    <div><strong>Thi thử theo hạng</strong></div>
</header>

<div class="wrap">
    <aside class="card">
        <div class="row">
            <select id="selHang" class="select"></select>
            <button id="btnStart" class="btn primary">Bắt đầu</button>
        </div>
        <div class="row" style="justify-content:space-between;margin-top:8px">
            <div>Thời gian: <span id="tg">--</span> phút</div>
            <div class="timer" id="timer">00:00</div>
        </div>
        <hr/>
        <div class="grid" id="grid"></div>
        <div class="muted" style="margin-top:8px">* Ô viền đỏ là câu liệt</div>
    </aside>

    <main class="card">
        <div id="panelWelcome">
            <h3>Chọn hạng rồi nhấn Bắt đầu</h3>
            <p class="muted">Sai <b>câu liệt</b> sẽ <b>rớt</b> dù đủ điểm.</p>
        </div>

        <div id="panelExam" style="display:none">
            <div class="row" style="justify-content:space-between">
                <div><b>Câu <span id="idx">1</span>/<span id="total">--</span></b></div>
                <div class="muted">Chọn xong vẫn có thể đổi trước khi nộp</div>
            </div>
            <div class="qtext" id="qtext"></div>
            <div class="images" id="qimgs"></div>
            <div class="answers" id="answers"></div>
            <div class="row" style="margin-top:10px">
                <button id="btnPrev" class="btn">&laquo; Trước</button>
                <button id="btnNext" class="btn">Sau &raquo;</button>
                <div style="flex:1"></div>
                <button id="btnSubmit" class="btn primary">Nộp bài</button>
            </div>
        </div>

        <div id="panelResult" style="display:none">
            <div id="resTitle" class="result"></div>
            <p id="resDetail" class="muted"></p>
            <div id="resWrong"></div>
        </div>
    </main>
</div>

<script>
async function loadPresets() {
  const res = await fetch('/api/thi/preset');
  const data = await res.json();
  const presets = data.presets || {};
  const loaiBang = Array.isArray(data.loai_bang) ? data.loai_bang : [];

  selHang.innerHTML = '';
  // 1) Ưu tiên presets
  const presetKeys = Object.keys(presets);
  if (presetKeys.length > 0) {
    presetKeys.forEach(code => {
      const opt = document.createElement('option');
      opt.value = code;
      opt.textContent = code;
      opt.dataset.tg = presets[code]?.thoi_gian ?? '';
      selHang.appendChild(opt);
    });
  } else if (loaiBang.length > 0) {
    // 2) Fallback: lấy từ bảng loại bằng
    loaiBang.forEach(item => {
      const code = (item.ma || item.ten || '').toString().trim();
      if (!code) return;
      const opt = document.createElement('option');
      opt.value = code.toUpperCase();
      opt.textContent = code.toUpperCase();
      // không có preset → để trống, khi Start sẽ nhận từ server
      selHang.appendChild(opt);
    });
  } else {
    // Không có gì để hiển thị
    const opt = document.createElement('option');
    opt.textContent = 'Chưa có hạng';
    opt.disabled = true;
    opt.selected = true;
    selHang.appendChild(opt);
  }

  // Hiển thị thời gian dự kiến theo preset khi đổi hạng
  function updateTimeLabel() {
    const code = selHang.value;
    tg.textContent = presets[code]?.thoi_gian ?? '--';
  }
  selHang.onchange = updateTimeLabel;
  updateTimeLabel();
}
loadPresets();
</script>

</body>
</html>
