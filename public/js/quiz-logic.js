document.addEventListener('DOMContentLoaded', async function () {
  const gridEl = document.querySelector('.question-grid');
  const box    = document.getElementById('question-container');
  const prevBtn= document.getElementById('prev-btn');
  const nextBtn= document.getElementById('next-btn');

  let grid = [];   // [1,2,3,...]
  let idx  = 0;    // index trong grid
  const cache = {};

  const skeleton = `<div class="question-content"><p>Đang tải câu hỏi...</p></div>`;

  const tpl = (q) => {
    const imgs = (q.images||[]).map(im=>`
      <figure class="q-img">
        <img src="${im.url}" alt="${im.alt||''}" loading="lazy" decoding="async">
      </figure>`).join('');

    const answers = (q.cau_tra_lois||[]).map(a=>`
      <label class="option" data-correct="${a.caudung ? '1':'0'}">
        <input type="radio" name="answer-${q.id}" value="${a.id}">
        <span>${a.noidung}</span>
      </label>`).join('');

    return `
      <div class="question-content">
        <p class="question-text"><strong>Câu ${q.stt}:</strong> ${q.noidung}</p>
        ${imgs ? `<div class="question-images">${imgs}</div>` : ``}
        <div class="answer-options">${answers}</div>
        <div id="feedback" class="feedback"></div>
      </div>`;
  };

  async function getQuestion(stt){
    if (cache[stt]) return cache[stt];
    const res  = await fetch(`/api/cauhoi/${stt}`);
    if (!res.ok) throw new Error('HTTP '+res.status);
    const data = await res.json();
    cache[stt] = data;
    return data;
  }

  async function show(i){
    if (i<0 || i>=grid.length) return;
    idx = i;
    box.innerHTML = skeleton;

    try {
      const stt = grid[i];
      const q   = await getQuestion(stt);
      box.innerHTML = tpl(q);

      // trạng thái nút
      prevBtn.disabled = i===0;
      nextBtn.disabled = i===grid.length-1;

      // prefetch câu kế
      if (grid[i+1]) getQuestion(grid[i+1]).catch(()=>{});
    } catch (e) {
      box.innerHTML = `<p>Lỗi tải dữ liệu. Vui lòng thử lại.</p>`;
      console.error(e);
    }
  }

  // tô xanh/đỏ khi chọn
  box.addEventListener('change', (e) => {
    const input = e.target.closest('input[type="radio"]');
    if (!input) return;
    const wrap = input.closest('.answer-options');
    if (!wrap) return;

    // khoá lại
    wrap.querySelectorAll('input[type="radio"]').forEach(r => r.disabled = true);

    const chosen = input.closest('.option');
    const isCorrect = chosen.dataset.correct === '1' || chosen.dataset.correct === 'true';
    const fb = box.querySelector('#feedback');

    if (isCorrect) {
      chosen.classList.add('correct');
      if (fb) fb.textContent = '✅ Chính xác!';
    } else {
      chosen.classList.add('wrong');
      const right = wrap.querySelector('.option[data-correct="1"], .option[data-correct="true"]');
      if (right) right.classList.add('correct');
      if (fb) fb.textContent = '❌ Chưa đúng!';
    }
  });

  // dựng lưới
  const res = await fetch('/api/grid');
  grid = await res.json();
  gridEl.innerHTML = grid.map((stt,i)=>`<a href="#" class="question-number" data-i="${i}">${stt}</a>`).join('');
  gridEl.addEventListener('click', (e)=>{
    const a = e.target.closest('.question-number'); if(!a) return;
    e.preventDefault(); show(+a.dataset.i);
  });

  prevBtn.onclick = ()=> show(idx-1);
  nextBtn.onclick = ()=> show(idx+1);

  show(0);
});
