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
const selHang = document.getElementById('selHang');
const btnStart = document.getElementById('btnStart');
const btnStart20 = document.getElementById('btnStart20');
const tg = document.getElementById('tg');
const timerEl = document.getElementById('timer');

const grid = document.getElementById('grid');
const panelWelcome = document.getElementById('panelWelcome');
const panelExam = document.getElementById('panelExam');
const panelResult = document.getElementById('panelResult');
const qtext = document.getElementById('qtext');
const qimgs = document.getElementById('qimgs');
const answersEl = document.getElementById('answers');
const idxEl = document.getElementById('idx');
const totalEl = document.getElementById('total');
const btnPrev = document.getElementById('btnPrev');
const btnNext = document.getElementById('btnNext');
const btnSubmit = document.getElementById('btnSubmit');
const resTitle = document.getElementById('resTitle');
const resDetail = document.getElementById('resDetail');
const resWrong = document.getElementById('resWrong');

let presetMap = {};
let exam = null;
let examId = null;
let currentIndex = 0;
let selections = {}; // question_id -> answer_id
let expiresAt = null;
let timerTick = null;

// --- Load preset từ /api/thi/preset (fallback nếu lỗi) ---
async function loadPresets() {
  try {
    const res = await fetch('/api/thi/preset');
    if (!res.ok) throw new Error('preset api not ok');
    const data = await res.json();
    presetMap = data.presets || {};
  } catch {
    // fallback nếu API lỗi
    presetMap = { A1:{}, A:{}, B1:{}, B:{}, C1:{} };
  }
  selHang.innerHTML = '';
  Object.keys(presetMap).forEach(code => {
    const opt = document.createElement('option');
    opt.value = code; opt.textContent = code;
    selHang.appendChild(opt);
  });
}
loadPresets();

function mustChooseHang() {
  if (!selHang.value) { alert('Vui lòng chọn hạng thi trước.'); selHang.focus(); return true; }
  return false;
}

// --- HÀM DÙNG CHUNG: tạo đề theo preset hoặc 20 câu ---
async function startExam(options = {}) {
  if (mustChooseHang()) return;

  const payload = { hang: selHang.value };
  if (options.so_cau) payload.so_cau = options.so_cau; // ví dụ 20 câu

  const res = await fetch('/api/thi/tao-de', {
    method:'POST',
    headers:{
      'Content-Type':'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  if (!res.ok) {
    // Hiển thị message thật từ server
    const raw = await res.text();
    let msg = 'Lỗi tạo đề';
    try { const j = JSON.parse(raw); if (j && j.message) msg = j.message; } catch { if (raw) msg = raw; }
    console.error('Tạo đề lỗi:', msg, 'HTTP', res.status, raw);
    alert(msg);
    return;
  }

  exam = await res.json();
  examId = exam.exam_id;
  expiresAt = exam.expires_at;

  tg.textContent = exam.thoi_gian_phut ?? '--';
  totalEl.textContent = exam.so_cau ?? '--';

  panelWelcome.style.display = 'none';
  panelResult.style.display = 'none';
  panelExam.style.display = '';
  currentIndex = 0;
  selections = {};

  renderGrid();
  renderQuestion();
  startTimer();
}

function renderGrid() {
  grid.innerHTML = '';
  exam.questions.forEach((q, i) => {
    const d = document.createElement('div');
    d.className = 'num' + (q.is_liet ? ' liet' : '');
    d.textContent = (i+1);
    d.onclick = () => { currentIndex = i; renderQuestion(); };
    grid.appendChild(d);
  });
  setActiveNum();
}

function setActiveNum() {
  Array.from(grid.children).forEach((el, i) => {
    el.classList.toggle('active', i === currentIndex);
  });
}

function renderQuestion() {
  const q = exam.questions[currentIndex];
  idxEl.textContent = currentIndex+1;
  qtext.textContent = q.text || '(Không có nội dung câu hỏi)';
  qimgs.innerHTML = '';
  (q.images || []).forEach(img => {
    const el = document.createElement('img');
    el.src = img.url;
    el.alt = img.ten || '';
    qimgs.appendChild(el);
  });

  answersEl.innerHTML = '';
  (q.answers || []).forEach(a => {
    const el = document.createElement('div');
    el.className = 'ans';
    el.textContent = a.text || '(Không có nội dung đáp án)';
    if (selections[q.id] === a.id) el.classList.add('selected');
    el.onclick = () => { selections[q.id] = a.id; renderQuestion(); };
    answersEl.appendChild(el);
  });

  btnPrev.disabled = (currentIndex === 0);
  btnNext.disabled = (currentIndex >= exam.questions.length - 1);
  setActiveNum();
}

function formatMMSS(remainSec) {
  const m = Math.floor(remainSec / 60);
  const s = Math.floor(remainSec % 60);
  return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
}

function startTimer() {
  clearInterval(timerTick);
  timerTick = setInterval(() => {
    const t = Math.max(0, (new Date(expiresAt).getTime() - Date.now()) / 1000);
    timerEl.textContent = formatMMSS(t);
    if (t <= 0) { clearInterval(timerTick); doSubmit(true); }
  }, 250);
}

// Nút bắt đầu theo preset
btnStart.onclick = () => startExam();
// Nút Ôn tập 20 đề
btnStart20.onclick = () => startExam({ so_cau: 20 });

btnPrev.onclick = () => { if (currentIndex > 0) { currentIndex--; renderQuestion(); } };
btnNext.onclick = () => { if (currentIndex < exam.questions.length-1) { currentIndex++; renderQuestion(); } };

async function doSubmit(auto = false) {
  btnSubmit.disabled = true;
  const payload = {
    exam_id: examId,
    answers: Object.entries(selections).map(([qid, aid]) => ({
      question_id: Number(qid), answer_id: Number(aid)
    }))
  };
  const res = await fetch('/api/thi/nop-bai', {
    method:'POST',
    headers:{'Content-Type':'application/json','Accept':'application/json'},
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  panelExam.style.display = 'none';
  panelResult.style.display = '';

  // Thông báo kết thúc
  setTimeout(() => alert('Đã kết thúc bài thi'), 50);

  if (data.passed) {
    resTitle.className = 'result pass';
    resTitle.textContent = 'ĐẬU';
    resDetail.textContent = `Điểm: ${data.correct}/${data.total}. Yêu cầu tối thiểu: ${data.required}.`;
  } else {
    resTitle.className = 'result fail';
    resTitle.textContent = 'RỚT';
    const reason = data.reason ? `Lý do: ${data.reason}.` : '';
    resDetail.textContent = `Điểm: ${data.correct}/${data.total}. ${reason}`;
  }

  if (data.expired) {
    const p = document.createElement('p');
    p.className = 'muted';
    p.textContent = '(Bài nộp sau khi hết giờ)';
    resDetail.after(p);
  }

  resWrong.innerHTML = '';
  if (Array.isArray(data.wrong_question_ids) && data.wrong_question_ids.length > 0) {
    const h = document.createElement('h4'); h.textContent = 'Các câu trả lời sai:'; resWrong.appendChild(h);
    const ul = document.createElement('ul');
    data.wrong_question_ids.forEach(qid => {
      const idx = exam.questions.findIndex(q => q.id === qid);
      const sttInPaper = idx >= 0 ? (idx+1) : '?';
      const isLiet = idx >= 0 ? !!exam.questions[idx].is_liet : false;
      const item = document.createElement('li');
      item.textContent = `Câu ${sttInPaper}` + (isLiet ? ' (liệt)' : '');
      ul.appendChild(item);
    });
    resWrong.appendChild(ul);
  }

  clearInterval(timerTick);
}
btnSubmit.onclick = () => doSubmit(false);
</script>
</body>
</html>
