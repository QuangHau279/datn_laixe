// public/js/thi-thu.js

// ====== DOM refs ======
const selHang   = document.getElementById('selHang');
const btnStart  = document.getElementById('btnStart');
const selDe     = document.getElementById('selDe');
const deGroup   = document.getElementById('deGroup');
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
let selectedDe   = 'RANDOM';

// Review mode
let reviewMode = false; // đang xem lại sau khi nộp
let wrongIds   = [];    // danh sách câu sai (id câu)
let lietWrong  = false; // có sai câu liệt không

// ==== Thêm state để lưu đáp án đúng và lựa chọn của user sau khi nộp ====
let correctMap = {};  // { qid: [aid,...] }
let userMap    = {};  // { qid: aid }

// Lưu exam data để xem lại sau khi kết thúc
let reviewExam = null;  // exam data đã kết thúc để xem lại

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

// ====== Tạo nhóm button ĐỀ dựa trên preset ======
function updateDeButtons() {
  if (!deGroup) return;
  const hang = getHangValue();
  if (!hang || !presetMap[hang]) {
    deGroup.innerHTML = '<p class="muted" style="text-align:center">Chọn hạng để xem các bộ đề</p>';
    return;
  }
  
  const preset = presetMap[hang];
  const deOpts = preset.de_options || [];
  
  if (deOpts.length === 0) {
    deGroup.innerHTML = '<p class="muted" style="text-align:center">Hạng này chưa có bộ đề</p>';
    return;
  }
  
  // Sắp xếp: RANDOM đầu tiên, sau đó 1..5
  const sortedOpts = deOpts.slice().sort((a, b) => {
    if (a === 'RANDOM') return -1;
    if (b === 'RANDOM') return 1;
    return Number(a) - Number(b);
  });
  
  deGroup.innerHTML = '';
  sortedOpts.forEach((opt, idx) => {
    const v = String(opt);
    const t = v === 'RANDOM' ? 'Ngẫu nhiên' : `Đề ${v}`;
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'de-btn' + (idx === 0 ? ' active' : '');
    b.dataset.de = v;
    b.textContent = t;
    deGroup.appendChild(b);
    
    if (idx === 0) selectedDe = v; // Mặc định chọn option đầu tiên
    
    b.addEventListener('click', () => {
      // Không cho phép chọn đề khi đang thi
      if (exam && examId) {
        alert('Vui lòng kết thúc bài thi hiện tại trước khi chọn đề khác.');
        return;
      }
      
      selectedDe = v;
      [...deGroup.querySelectorAll('.de-btn')].forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      // Chỉ chọn đề, không tự động bắt đầu - người dùng phải bấm "Bắt đầu"
    });
  });
}

// ====== Fill Selects ======
function fillSelects() {
  function fillSelect(selectEl){
    if (!selectEl) {
      console.warn('fillSelect: selectEl is null');
      return;
    }
    selectEl.innerHTML = '<option value="">-- Chọn hạng --</option>';
    
    const keys = Object.keys(presetMap);
    console.log('fillSelects: presetMap keys:', keys);
    
    if (keys.length === 0) {
      console.warn('fillSelects: presetMap is empty!');
      return;
    }
    
    keys.forEach(code => {
      const opt = document.createElement('option');
      opt.value = code;
      opt.textContent = code;
      selectEl.appendChild(opt);
    });
    
    console.log('fillSelects: Added', keys.length, 'options to select');
  }
  fillSelect(selHang);
  fillSelect(selHangM);
}

// ====== Presets ======
async function loadPresets() {
  console.log('loadPresets: Starting...');
  try {
    const res = await fetch('/api/thi/preset', { credentials: 'same-origin' });
    if (!res.ok) {
      console.error('loadPresets: API response not OK', res.status, res.statusText);
      throw new Error('preset api not ok: ' + res.status);
    }
    const data = await res.json();
    console.log('loadPresets: API response:', data);
    presetMap = data && data.presets ? data.presets : {};
    console.log('loadPresets: presetMap after assignment:', presetMap);
    
    if (Object.keys(presetMap).length === 0) {
      console.warn('loadPresets: presetMap is empty, using fallback');
      presetMap = { A1:{}, A:{}, B1:{}, B:{}, C1:{} };
    }
  } catch (e) {
    console.error('loadPresets: Error loading presets', e);
    presetMap = { A1:{}, A:{}, B1:{}, B:{}, C1:{} };
  }

  // đổ option cho cả desktop & mobile (nếu có)
  console.log('loadPresets: Calling fillSelects()...');
  fillSelects();
  
  // Đảm bảo select hạng enable khi chưa bắt đầu thi
  enableControls();
  
  // Đồng bộ khi thay đổi select (desktop và mobile) + cập nhật buttons
  if (selHang && selHangM) {
    selHang.addEventListener('change', function() {
      // Không cho phép thay đổi hạng khi đang thi
      if (exam && examId) {
        this.value = getHangValue(); // Khôi phục giá trị cũ
        alert('Vui lòng kết thúc bài thi hiện tại trước khi chọn hạng khác.');
        return;
      }
      selHangM.value = this.value;
      updateDeButtons(); // Cập nhật buttons khi đổi hạng
    });
    selHangM.addEventListener('change', function() {
      // Không cho phép thay đổi hạng khi đang thi
      if (exam && examId) {
        this.value = getHangValue(); // Khôi phục giá trị cũ
        alert('Vui lòng kết thúc bài thi hiện tại trước khi chọn hạng khác.');
        return;
      }
      selHang.value = this.value;
      updateDeButtons(); // Cập nhật buttons khi đổi hạng
    });
  } else {
    // Nếu chỉ có một trong hai select
    if (selHang) {
      selHang.addEventListener('change', function() {
        // Không cho phép thay đổi hạng khi đang thi
        if (exam && examId) {
          this.value = getHangValue(); // Khôi phục giá trị cũ
          alert('Vui lòng kết thúc bài thi hiện tại trước khi chọn hạng khác.');
          return;
        }
        updateDeButtons();
      });
    }
    if (selHangM) {
      selHangM.addEventListener('change', function() {
        // Không cho phép thay đổi hạng khi đang thi
        if (exam && examId) {
          this.value = getHangValue(); // Khôi phục giá trị cũ
          alert('Vui lòng kết thúc bài thi hiện tại trước khi chọn hạng khác.');
          return;
        }
        updateDeButtons();
      });
    }
  }
  
  // Khởi tạo ban đầu sau khi load preset
  updateDeButtons();
  
  // Đảm bảo select hạng enable sau khi load xong
  enableControls();
  
  console.log('loadPresets: Completed. presetMap has', Object.keys(presetMap).length, 'items');
}

// Đảm bảo load presets sau khi DOM sẵn sàng
// Và đảm bảo select hạng enable ngay từ đầu
function initializePage() {
  console.log('initializePage: Starting initialization...');
  
  // Enable controls ngay từ đầu
  if (selHang) selHang.disabled = false;
  if (selHangM) selHangM.disabled = false;
  if (btnStart) btnStart.disabled = false;
  if (btnStartM) btnStartM.disabled = false;
  
  // Load presets
  loadPresets();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded: Initializing page...');
    initializePage();
  });
} else {
  // DOM đã sẵn sàng
  console.log('DOM ready: Initializing page...');
  initializePage();
}

function mustChooseHang(){
  const value = getHangValue();
  if (!value) {
    alert('Vui lòng chọn hạng thi trước.');
    (selHangM || selHang)?.focus();
    return true;
  }
  return false;
}

// ====== Disable/Enable Controls ======
function disableControls() {
  // Disable select hạng
  if (selHang) selHang.disabled = true;
  if (selHangM) selHangM.disabled = true;
  
  // Disable nút bắt đầu
  if (btnStart) btnStart.disabled = true;
  if (btnStartM) btnStartM.disabled = true;
  
  // Disable các button đề
  if (deGroup) {
    deGroup.querySelectorAll('.de-btn').forEach(btn => {
      btn.disabled = true;
      btn.style.opacity = '0.5';
      btn.style.cursor = 'not-allowed';
    });
  }
}

function enableControls() {
  // Enable select hạng
  if (selHang) selHang.disabled = false;
  if (selHangM) selHangM.disabled = false;
  
  // Enable nút bắt đầu
  if (btnStart) btnStart.disabled = false;
  if (btnStartM) btnStartM.disabled = false;
  
  // Enable các button đề
  if (deGroup) {
    deGroup.querySelectorAll('.de-btn').forEach(btn => {
      btn.disabled = false;
      btn.style.opacity = '1';
      btn.style.cursor = 'pointer';
    });
  }
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

  // Kiểm tra xem hạng có đề không
  const preset = presetMap[hang];
  if (!preset) {
    alert('Hạng này không hợp lệ. Vui lòng chọn lại.');
    return;
  }

  const deOpts = preset.de_options || [];
  const totalQuestions = preset.total_questions || 0;

  // Kiểm tra xem có đề không
  if (deOpts.length === 0 && totalQuestions === 0) {
    alert('Hạng này chưa có bộ đề. Vui lòng chọn hạng khác.');
    return;
  }

  // Kiểm tra nếu đang thi thì không cho bắt đầu đề mới
  if (exam && examId) {
    if (!confirm('Bạn đang có một bài thi chưa hoàn thành. Bạn có muốn bỏ bài thi hiện tại và bắt đầu bài thi mới?')) {
      return;
    }
    // Reset bài thi cũ
    exam = null;
    examId = null;
    expiresAt = null;
    clearInterval(timerTick);
  }
  
  console.log('Bắt đầu thi với hạng:', hang);

  // reset review state
  reviewMode = false; wrongIds = []; lietWrong = false;
  if (revWrap) revWrap.style.display = 'none';
  if (revTbl)  revTbl.innerHTML = '';

  const payload = { hang };
  payload.de = (selDe && selDe.value) ? selDe.value : selectedDe;
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
  
  // Disable các control khi đang thi
  disableControls();
}
// lắng nghe cả 2 nút (nút mobile có thể không tồn tại)
btnStart && btnStart.addEventListener('click', startExam);
btnStartM && btnStartM.addEventListener('click', startExam);

// ====== Grid ======
function renderGrid() {
  grid.innerHTML = '';
  const currentExam = (reviewMode && reviewExam) ? reviewExam : exam;
  if (!currentExam || !currentExam.questions) return;
  
  currentExam.questions.forEach((q, i) => {
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

    // Cụm ô nhỏ 1..N theo số đáp án
    const opts = document.createElement('div');
    opts.className = 'g-opts';
    (q.answers || []).forEach((a, idx) => {
      const o = document.createElement('div');
      o.className = 'g-opt';
      o.textContent = String(idx + 1);
      o.title = 'Chọn đáp án ' + (idx + 1);
      o.dataset.qid = String(q.id);
      o.dataset.aid = String(a.id);
      if (selections[q.id] === a.id) o.classList.add('selected');

      if (!reviewMode) {
        o.addEventListener('click', (ev) => {
          ev.stopPropagation();                 // không kích hoạt click số câu
          selections[q.id] = a.id;              // chọn đáp án
          renderQuestion();                     // vẽ lại vùng bên phải
          renderGrid();                         // vẽ lại lưới để cập nhật ô nhỏ
        });
      }
      opts.appendChild(o);
    });
    item.appendChild(opts);

    grid.appendChild(item);
  });

  // nếu đang review, đánh dấu màu
  if (reviewMode) markGridAfterSubmit();

  setActiveNum();
}

function setActiveNum() {
  const items = [...grid.querySelectorAll('.g-item')];
  const currentExam = (reviewMode && reviewExam) ? reviewExam : exam;
  if (!currentExam || !currentExam.questions) return;
  
  items.forEach((it, i) => {
    it.classList.toggle('active', i === currentIndex);

    const qid = currentExam.questions[i].id;
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
  // Sử dụng reviewExam nếu đang ở chế độ review và exam đã bị reset
  const currentExam = (reviewMode && reviewExam) ? reviewExam : exam;
  if (!currentExam || !currentExam.questions) return;
  
  const q = currentExam.questions[currentIndex];
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

  // currentExam đã được khai báo ở đầu hàm, không cần khai báo lại
  const totalQuestions = currentExam ? currentExam.questions.length : 0;
  
  btnPrev && (btnPrev.disabled = (currentIndex === 0));
  btnNext && (btnNext.disabled = (currentIndex >= totalQuestions - 1));
  setActiveNum();
}

// ====== Prev/Next ======
btnPrev?.addEventListener('click', () => {
  if (currentIndex > 0) { currentIndex--; renderQuestion(); }
});
btnNext?.addEventListener('click', () => {
  const currentExam = (reviewMode && reviewExam) ? reviewExam : exam;
  if (currentExam && currentIndex < currentExam.questions.length - 1) { 
    currentIndex++; 
    renderQuestion(); 
  }
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
  const currentExam = (reviewMode && reviewExam) ? reviewExam : exam;
  if (!currentExam || !currentExam.questions) return;
  
  [...grid.children].forEach((el, i) => {
    const q = currentExam.questions[i];
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
  const currentExam = (reviewMode && reviewExam) ? reviewExam : exam;
  if (!currentExam || !currentExam.questions) return 0;
  return currentExam.questions.reduce((m, q) => Math.max(m, (q.answers||[]).length), 0);
}

function renderReviewMatrix() {
  const wrap = document.getElementById('reviewMatrix');
  const tbl  = document.getElementById('revTbl');
  if (!wrap || !tbl) return;

  const currentExam = (reviewMode && reviewExam) ? reviewExam : exam;
  if (!currentExam || !currentExam.questions) return;

  // Tính số cột tối đa theo số phương án của câu dài nhất
  const maxA = currentExam.questions.reduce((m, q) => Math.max(m, (q.answers||[]).length), 0);

  // Header
  let thead = '<thead><tr><th style="text-align:left">Câu hỏi</th>';
  for (let i=1;i<=maxA;i++) thead += `<th>Đáp án ${i}</th>`;
  thead += '<th style="text-align:left">Ghi chú</th></tr></thead>';

  // Body: CHỈ đánh dấu đáp án đúng; KHÔNG hiển thị đáp án đã chọn
  let tbody = '<tbody>';
  currentExam.questions.forEach((q, i) => {
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
      currentIndex = idx;
      
      // Hiện panel exam để xem lại câu hỏi
      panelExam.style.display = '';
      panelResult.style.display = '';
      
      renderQuestion(); 
      setActiveNum();
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

  // Lưu exam data để xem lại
  reviewExam = exam;

  // Không hiện panel exam ở đây - sẽ được điều khiển bởi doSubmit
  btnSubmit.disabled = true;
  btnStart && (btnStart.disabled  = true);
  btnStartM && (btnStartM.disabled = true);

  clearInterval(timerTick);
  timerEl.textContent = '00:00';

  markGridAfterSubmit();
  renderReviewMatrix();

  // Nhảy tới câu sai đầu tiên nếu có
  const currentExam = (reviewMode && reviewExam) ? reviewExam : exam;
  if (currentExam && currentExam.questions) {
    const firstWrongIdx = currentExam.questions.findIndex(q => wrongIds.includes(q.id));
    if (firstWrongIdx >= 0) {
      currentIndex = firstWrongIdx;
      renderQuestion();
      setActiveNum();
    }
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
  // Lưu ý: enterReviewMode sẽ lưu exam vào reviewExam
  enterReviewMode(data);
  
  // Ẩn panel exam, hiện cả panel result và welcome
  // Panel result để xem kết quả, panel welcome để chọn đề mới
  panelExam.style.display = 'none';
  panelResult.style.display = '';
  panelWelcome.style.display = ''; // Hiện lại panel welcome để chọn đề mới (có buttons đề)
  
  // Dừng timer
  clearInterval(timerTick);
  
  // Reset exam state để có thể chọn đề mới
  // exam data đã được lưu vào reviewExam trong enterReviewMode
  exam = null;
  examId = null;
  expiresAt = null;
  selections = {}; // Reset selections
  currentIndex = 0;
  
  // Enable lại các control sau khi kết thúc (cho phép chọn hạng/đề mới)
  // Phải enable sau khi reset exam để không bị chặn bởi logic kiểm tra exam
  enableControls();
  
  // Đảm bảo select hạng có đầy đủ options
  // Luôn populate lại select hạng sau khi kết thúc để đảm bảo có đầy đủ options
  if (Object.keys(presetMap).length > 0) {
    fillSelects();
  } else {
    // Nếu presetMap rỗng, load lại presets
    loadPresets();
  }
  
  // Đảm bảo select hạng và button đề hoạt động bình thường
  // Cập nhật lại buttons đề dựa trên hạng hiện tại
  updateDeButtons();
  
  // Đảm bảo select hạng hiển thị và có thể chọn được
  if (selHang) {
    selHang.style.display = '';
    selHang.disabled = false;
  }
  if (selHangM) {
    selHangM.style.display = '';
    selHangM.disabled = false;
  }
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
