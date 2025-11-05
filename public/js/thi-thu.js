// public/js/thi-thu.js

// ====== DOM refs ======
const selHang   = document.getElementById('selHang');
const btnStart  = document.getElementById('btnStart');
const selDe     = document.getElementById('selDe');
// Mobile controls (nếu có trong DOM)
const selHangM  = document.getElementById('selHangM');
const btnStartM = document.getElementById('btnStartM');

const tg        = document.getElementById('tg');
const timerEl   = document.getElementById('timer');

const grid      = document.getElementById('grid');
const panelWelcome = document.getElementById('panelWelcome');
const panelExam    = document.getElementById('panelExam');
const panelResult  = document.getElementById('panelResult');
const qtext     = document.getElementById('qtext');
const qimgs     = document.getElementById('qimgs');
const answersEl = document.getElementById('answers');
const idxEl     = document.getElementById('idx');
const totalEl   = document.getElementById('total');
const btnPrev   = document.getElementById('btnPrev');
const btnNext   = document.getElementById('btnNext');
const btnSubmit = document.getElementById('btnSubmit');
const resTitle  = document.getElementById('resTitle');
const resDetail = document.getElementById('resDetail');
const revWrap   = document.getElementById('reviewMatrix');
const revTbl    = document.getElementById('revTbl');

// ====== State ======
let presetMap   = {};
let exam        = null;
let examId      = null;
let currentIndex = 0;
let selections   = {}; // question_id -> answer_id
let expiresAt    = null;
let timerTick    = null;

// Review mode
let reviewMode = false; // đang xem lại sau khi nộp
let wrongIds   = [];    // danh sách câu sai (id câu)
let lietWrong  = false; // có sai câu liệt không

// ==== Thêm state để lưu đáp án đúng và lựa chọn của user sau khi nộp ====
let correctMap = {};  // { qid: [aid,...] }
let userMap    = {};  // { qid: aid }

// ====== Helpers (CSRF + POST) ======
const CSRF_META = document.querySelector('meta[name="csrf-token"]');
const CSRF = CSRF_META ? CSRF_META.getAttribute('content') : '';

async function postJSON(url, payload) {
  return fetch(url, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': CSRF,
    },
    credentials: 'same-origin', // gửi cookie session
    body: JSON.stringify(payload),
  });
}

// Helper: lấy giá trị hạng (ưu tiên mobile nếu có)
function getHangValue() {
  // Kiểm tra mobile select trước
  if (selHangM && selHangM.value) {
    // Đồng bộ với desktop select nếu có
    if (selHang) selHang.value = selHangM.value;
    return selHangM.value;
  }
  // Nếu không có mobile, dùng desktop
  if (selHang && selHang.value) {
    // Đồng bộ với mobile select nếu có
    if (selHangM) selHangM.value = selHang.value;
    return selHang.value;
  }
  return '';
}

// ====== Presets ======
async function loadPresets() {
  try {
    const res = await fetch('/api/thi/preset', { credentials: 'same-origin' });
    if (!res.ok) throw new Error('preset api not ok');
    const data = await res.json();
    presetMap = data && data.presets ? data.presets : {};
  } catch (e) {
    console.warn('Preset error', e);
    presetMap = { A1:{}, A:{}, B1:{}, B:{}, C1:{} };
  }

  // đổ option cho cả desktop & mobile (nếu có)
  function fillSelect(selectEl){
    if (!selectEl) return;
    selectEl.innerHTML = '<option value="">-- Chọn hạng --</option>';
    Object.keys(presetMap).forEach(code => {
      const opt = document.createElement('option');
      opt.value = code;
      opt.textContent = code;
      selectEl.appendChild(opt);
    });
  }
  fillSelect(selHang);
  fillSelect(selHangM);

  // đổ options cho chọn ĐỀ (5 bộ + Ngẫu nhiên)
  if (selDe) {
    selDe.innerHTML = '';
    const opts = [
      {v:'RANDOM', t:'Đề ngẫu nhiên'},
      {v:'1', t:'Đề 1'},
      {v:'2', t:'Đề 2'},
      {v:'3', t:'Đề 3'},
      {v:'4', t:'Đề 4'},
      {v:'5', t:'Đề 5'},
    ];
    opts.forEach(o => {
      const el = document.createElement('option');
      el.value = o.v; el.textContent = o.t; selDe.appendChild(el);
    });
    selDe.value = 'RANDOM';
  }
  
  // Đồng bộ khi thay đổi select
  if (selHang && selHangM) {
    selHang.addEventListener('change', function() {
      selHangM.value = this.value;
    });
    selHangM.addEventListener('change', function() {
      selHang.value = this.value;
    });
  }
}
loadPresets();

function mustChooseHang(){
  const value = getHangValue();
  if (!value) {
    alert('Vui lòng chọn hạng thi trước.');
    (selHangM || selHang)?.focus();
    return true;
  }
  return false;
}

// ====== Timer ======
function formatMMSS(sec){
  const m = Math.floor(sec/60), s = Math.floor(sec%60);
  return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
}
function startTimer(){
  clearInterval(timerTick);
  timerTick = setInterval(() => {
    const remain = Math.max(0, (new Date(expiresAt).getTime() - Date.now())/1000);
    timerEl.textContent = formatMMSS(remain);
    if (remain <= 0) { clearInterval(timerTick); doSubmit(true); }
  }, 250);
}

// ====== Start Exam ======
async function startExam() {
  if (mustChooseHang()) return;

  // Lấy giá trị hạng và đảm bảo đồng bộ
  const hang = getHangValue();
  
  // Đồng bộ cả 2 select
  if (selHang && selHang.value !== hang) selHang.value = hang;
  if (selHangM && selHangM.value !== hang) selHangM.value = hang;
  
  // Kiểm tra lại giá trị
  if (!hang) {
    alert('Vui lòng chọn hạng thi trước.');
    return;
  }
  
  console.log('Bắt đầu thi với hạng:', hang);

  // reset review state
  reviewMode = false; wrongIds = []; lietWrong = false;
  if (revWrap) revWrap.style.display = 'none';
  if (revTbl)  revTbl.innerHTML = '';

  const payload = { hang };
  if (selDe && selDe.value) payload.de = selDe.value;
  const res = await postJSON('/api/thi/tao-de', payload);

  if (!res.ok) {
    let msg = 'Lỗi tạo đề';
    try {
      const t = await res.text();
      const j = JSON.parse(t);
      msg = j.message || t;
    } catch { /* ignore */ }
    alert(msg);
    return;
  }

  const data = await res.json();
  if (!data || !Array.isArray(data.questions)) {
    alert('Dữ liệu đề không hợp lệ.');
    return;
  }

  exam      = data;
  examId    = data.exam_id;
  expiresAt = data.expires_at;

  tg.textContent      = data.thoi_gian_phut ?? '--';
  totalEl.textContent = data.so_cau ?? '--';

  panelWelcome.style.display = 'none';
  panelResult.style.display  = 'none';
  panelExam.style.display    = '';

  currentIndex = 0;
  selections  = {};

  renderGrid();
  renderQuestion();
  startTimer();
}
// lắng nghe cả 2 nút (nút mobile có thể không tồn tại)
btnStart && btnStart.addEventListener('click', startExam);
btnStartM && btnStartM.addEventListener('click', startExam);

// ====== Grid ======
function renderGrid() {
  grid.innerHTML = '';
  exam.questions.forEach((q, i) => {
    const item  = document.createElement('div');
    item.className = 'g-item' + (q.is_liet ? ' liet' : '');
    item.dataset.index = String(i);

    // Nút số câu
    const no = document.createElement('button');
    no.type = 'button';
    no.className = 'g-no';
    no.textContent = (i + 1);
    const qid = q.id;
    if (selections[qid]) no.classList.add('answered');
    no.addEventListener('click', () => { currentIndex = i; renderQuestion(); setActiveNum(); });
    item.appendChild(no);

    // Bỏ cụm ô chọn nhỏ; lựa chọn sẽ thực hiện bằng nút ở giữa màn hình

    grid.appendChild(item);
  });

  // nếu đang review, đánh dấu màu
  if (reviewMode) markGridAfterSubmit();

  setActiveNum();
}

function setActiveNum() {
  const items = [...grid.querySelectorAll('.g-item')];
  items.forEach((it, i) => {
    it.classList.toggle('active', i === currentIndex);

    const qid = exam.questions[i].id;
    const noBtn = it.querySelector('.g-no');
    if (noBtn) noBtn.classList.toggle('answered', !!selections[qid]);

    const selectedAid = selections[qid];
    it.querySelectorAll('.g-opt').forEach(o => {
      o.classList.toggle('selected', Number(o.dataset.aid) === selectedAid);
    });
  });
}

// ====== Render Question ======
function renderQuestion() {
  const q = exam.questions[currentIndex];
  idxEl.textContent = currentIndex + 1;

  // Text
  qtext.textContent = q.text || '(Không có nội dung câu hỏi)';

  // Images (mặc định hiển thị 1 ảnh lớn – responsive)
  qimgs.innerHTML = '';
  const imgs = Array.isArray(q.images) ? q.images : [];

  if (imgs.length === 1) {
    qimgs.className = 'qimage has-one';
  } else if (imgs.length > 1) {
    qimgs.className = 'qimage has-many';
  } else {
    qimgs.className = 'qimage';
  }

  imgs.forEach(img => {
    const el = document.createElement('img');
    el.loading = 'lazy';
    el.decoding = 'async';
    el.src = img.url;
    el.alt = img.ten || 'Hình minh họa';
    qimgs.appendChild(el);
  });

  // Answers
  answersEl.innerHTML = '';
  (q.answers || []).forEach((a, idx) => {
    const el = document.createElement('div');
    el.className = 'ans';
    el.dataset.idx = String(idx + 1);
    el.dataset.aid = String(a.id);
    el.innerHTML = `<span class="ans-badge">${idx + 1}</span> <div>${a.text || '(Không có nội dung đáp án)'}</div>`;

    // đánh dấu đáp án đã chọn
    if (selections[q.id] === a.id) {
      el.classList.add('selected');
      if (reviewMode) el.classList.add('review-selected');
    }

    if (!reviewMode) {
      // đang làm bài: cho phép chọn
      el.addEventListener('click', () => {
        selections[q.id] = a.id;
        renderQuestion();
        setActiveNum();
      });
    } else {
      // sau khi nộp: chỉ xem lại
      el.classList.add('review-disabled');
    }

    answersEl.appendChild(el);
  });

  btnPrev && (btnPrev.disabled = (currentIndex === 0));
  btnNext && (btnNext.disabled = (currentIndex >= exam.questions.length - 1));
  setActiveNum();
}

// ====== Prev/Next ======
btnPrev?.addEventListener('click', () => {
  if (currentIndex > 0) { currentIndex--; renderQuestion(); }
});
btnNext?.addEventListener('click', () => {
  if (currentIndex < exam.questions.length - 1) { currentIndex++; renderQuestion(); }
});

// ====== Keyboard: 1..4 & arrows ======
document.addEventListener('keydown', (e) => {
  const tag = document.activeElement && document.activeElement.tagName;
  if (tag === 'INPUT' || tag === 'TEXTAREA') return;
  if (!panelExam || panelExam.style.display === 'none') return;

  if (!reviewMode && e.key >= '1' && e.key <= '4') {
    const el = answersEl.querySelector(`.ans[data-idx="${e.key}"]`);
    if (el) el.click();
  }
  if (e.key === 'ArrowRight') btnNext?.click();
  if (e.key === 'ArrowLeft')  btnPrev?.click();
});

// ====== Review helpers: đánh dấu grid sau khi nộp ======
function markGridAfterSubmit() {
  if (!exam) return;
  [...grid.children].forEach((el, i) => {
    const q = exam.questions[i];
    const chosen = userMap[q.id] ?? selections[q.id]; // ưu tiên server
    const isWrong = wrongIds.includes(q.id);
    el.classList.remove('ok','wrong','unanswered','liet-wrong');
    if (!chosen) {
      el.classList.add('unanswered');
    } else if (isWrong) {
      el.classList.add('wrong');
      if (q.is_liet) el.classList.add('liet-wrong');
    } else {
      el.classList.add('ok');
    }
  });
}

// ====== Bảng xem lại: hiển thị đáp án đúng & đã chọn ======
function getMaxAnswers() {
  return exam.questions.reduce((m, q) => Math.max(m, (q.answers||[]).length), 0);
}

function renderReviewMatrix() {
  const wrap = document.getElementById('reviewMatrix');
  const tbl  = document.getElementById('revTbl');
  if (!wrap || !tbl) return;

  // Tính số cột tối đa theo số phương án của câu dài nhất
  const maxA = exam.questions.reduce((m, q) => Math.max(m, (q.answers||[]).length), 0);

  // Header
  let thead = '<thead><tr><th style="text-align:left">Câu hỏi</th>';
  for (let i=1;i<=maxA;i++) thead += `<th>Đáp án ${i}</th>`;
  thead += '<th style="text-align:left">Ghi chú</th></tr></thead>';

  // Body: CHỈ đánh dấu đáp án đúng; KHÔNG hiển thị đáp án đã chọn
  let tbody = '<tbody>';
  exam.questions.forEach((q, i) => {
    const correctAids = Array.isArray(correctMap[q.id]) ? correctMap[q.id] : [];
    const chosenAid   = userMap[q.id] ?? selections[q.id] ?? null; // chỉ để ghi chú
    const isWrong     = wrongIds.includes(q.id);
    const hasChoose   = !!chosenAid;

    // Màu hàng
    const rowCls = !hasChoose ? 'rev-row-none' : (isWrong ? 'rev-row-wrong' : 'rev-row-right');

    tbody += `<tr class="${rowCls}" data-idx="${i}" style="cursor:pointer">`;
    tbody += `<td style="text-align:left">Câu ${i+1}${q.is_liet ? ' <span class="rev-liet">(liệt)</span>' : ''}</td>`;

    // Các cột đáp án: chỉ đánh dấu ô đúng
    for (let col=0; col<maxA; col++) {
      const opt = (q.answers || [])[col];
      if (!opt) { tbody += '<td></td>'; continue; }
      const isCorrect = correctAids.includes(opt.id);
      tbody += `<td>${isCorrect ? '<span class="rev-badge right">✓</span>' : ''}</td>`;
    }

    let note = 'Chưa chọn';
    if (hasChoose) note = isWrong ? `Sai${q.is_liet ? ' (liệt)' : ''}` : 'Đúng';
    tbody += `<td style="text-align:left">${note}</td>`;
    tbody += `</tr>`;
  });
  tbody += '</tbody>';

  tbl.innerHTML = thead + tbody;
  wrap.style.display = '';

  // Click một hàng → nhảy đến câu tương ứng
  tbl.querySelectorAll('tbody tr').forEach(tr => {
    tr.addEventListener('click', () => {
      const idx = Number(tr.dataset.idx || 0);
      currentIndex = idx; renderQuestion(); setActiveNum();
      const anchor = document.querySelector('#panelExam');
      if (anchor) window.scrollTo({ top: anchor.offsetTop - 20, behavior:'smooth' });
    });
  });
}

// ====== Enter review mode ======
function enterReviewMode(serverResult) {
  reviewMode = true;
  wrongIds   = Array.isArray(serverResult.wrong_question_ids) ? serverResult.wrong_question_ids : [];
  lietWrong  = !!serverResult.liet_wrong;

  // Lưu 2 map mới trả về
  correctMap = serverResult.correct_map || {};
  userMap    = serverResult.user_map    || selections || {};

  // giữ màn hình làm bài để xem lại
  panelExam.style.display = '';
  btnSubmit.disabled = true;
  btnStart && (btnStart.disabled  = true);
  btnStartM && (btnStartM.disabled = true);

  clearInterval(timerTick);
  timerEl.textContent = '00:00';

  markGridAfterSubmit();
  renderReviewMatrix();

  // Nhảy tới câu sai đầu tiên nếu có
  const firstWrongIdx = exam.questions.findIndex(q => wrongIds.includes(q.id));
  if (firstWrongIdx >= 0) {
    currentIndex = firstWrongIdx;
    renderQuestion();
    setActiveNum();
  }
}

// ====== Submit ======
async function doSubmit(auto=false){
  btnSubmit.disabled = true;

  const payload = {
    exam_id: examId,
    answers: Object.entries(selections).map(([qid, aid]) => ({
      question_id: Number(qid), answer_id: Number(aid)
    }))
  };

  let res;
  try {
    res = await postJSON('/api/thi/nop-bai', payload);
  } catch (err) {
    console.error('Submit error:', err);
    alert('Có lỗi khi nộp bài. Vui lòng thử lại.');
    btnSubmit.disabled = false;
    return;
  }

  if (!res.ok) {
    const text = await res.text();
    let msg = `Nộp bài thất bại (HTTP ${res.status}).`;
    try { const j = JSON.parse(text); if (j && j.message) msg = j.message; } catch {}
    alert(msg);
    btnSubmit.disabled = false;
    return;
  }

  const data = await res.json();

  // hiện kết quả chung
  panelResult.style.display = '';
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

  // Bật chế độ xem lại (sẽ vẽ bảng + tô màu grid)
  enterReviewMode(data);
}
btnSubmit && btnSubmit.addEventListener('click', () => doSubmit(false));

console.log('thi-thu.js loaded');

// --- (Tuỳ chọn) Menu header nhỏ ---
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('navToggle');
  const nav = document.getElementById('siteNav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', () => {
    const opened = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
  });

  nav.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      nav.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
});
