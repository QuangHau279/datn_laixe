// public/js/quiz-logic.js
document.addEventListener('DOMContentLoaded', async function () {
  const gridEl = document.querySelector('.question-grid');
  const box    = document.getElementById('question-container');
  const prevBtn= document.getElementById('prev-btn');
  const nextBtn= document.getElementById('next-btn');

  let grid = [];       // [1,2,3,...] -> stt
  let idx  = 0;        // index hiện tại trong grid[]
  const cache = {};    // cache câu theo stt

  const skeleton = `<div class="question-content"><p>Đang tải câu hỏi...</p></div>`;

  const tpl = (q) => {
    const imgs = (q.images||[]).map(im=>`
      <figure class="q-img">
        <img src="${im.url}" alt="${im.alt||''}" loading="lazy" decoding="async">
      </figure>`).join('');

    const answers = (q.cau_tra_lois||[]).map((a,i)=>`
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

  function setActiveCell() {
    // bỏ active cũ
    gridEl.querySelectorAll('.question-number').forEach(el => el.classList.remove('active-question'));
    // thêm active mới
    const active = gridEl.querySelector(`.question-number[data-i="${idx}"]`);
    if (active) active.classList.add('active-question');
  }

  function markGridAfterAnswer(isCorrect) {
    const cell = gridEl.querySelector(`.question-number[data-i="${idx}"]`);
    if (!cell) return;
    cell.classList.remove('answered-correct','answered-wrong');
    cell.classList.add(isCorrect ? 'answered-correct' : 'answered-wrong');
  }

  async function show(i){
    if (i<0 || i>=grid.length) return;
    idx = i;
    setActiveCell();

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

  // xử lý chọn đáp án -> tô màu trong nội dung + đánh dấu ô số câu
  box.addEventListener('change', (e) => {
    const input = e.target.closest('input[type="radio"]');
    if (!input) return;
    const wrap = input.closest('.answer-options');
    if (!wrap) return;

    // khoá lại
    wrap.querySelectorAll('input[type="radio"]').forEach(r => r.disabled = true);

    // reset trạng thái các option
    wrap.querySelectorAll('.option').forEach(o => o.classList.remove('correct','wrong'));

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

    // đánh dấu vào lưới
    markGridAfterAnswer(isCorrect);
  });

  // dựng lưới số câu
  try {
    const res = await fetch('/api/grid');
    grid = await res.json();
  } catch (e) {
    console.error(e);
    grid = [];
  }

  if (!Array.isArray(grid) || grid.length === 0) {
    box.innerHTML = `<p>Lỗi tải dữ liệu. Vui lòng tải lại trang.</p>`;
    return;
  }

  gridEl.innerHTML = grid.map((stt,i)=>
    `<a href="#" class="question-number" data-i="${i}">${stt}</a>`
  ).join('');

  gridEl.addEventListener('click', (e)=>{
    const a = e.target.closest('.question-number'); if(!a) return;
    e.preventDefault(); show(+a.dataset.i);
  });

  prevBtn.onclick = ()=> show(idx-1);
  nextBtn.onclick = ()=> show(idx+1);

  // khởi tạo
  show(0);

  // ========= TÌM KIẾM =========
  const searchInput = document.getElementById('qSearch');
  const searchResults = document.getElementById('search-results');
  let searchTimeout = null;

  async function performSearch(query) {
    if (!query || query.trim() === '') {
      if (searchResults) searchResults.style.display = 'none';
      return;
    }

    try {
      const res = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
      const data = await res.json();
      const items = data.items || [];

      if (!searchResults) return;

      if (items.length === 0) {
        searchResults.innerHTML = '<div class="search-no-results">Không tìm thấy kết quả</div>';
        searchResults.style.display = 'block';
        return;
      }

      // Render kết quả
      searchResults.innerHTML = items.map(item => `
        <div class="search-result-item" data-stt="${item.stt}">
          <div>
            <span class="search-result-stt">Câu ${item.stt}</span>
          </div>
          <div class="search-result-snippet">${item.snippet || ''}</div>
        </div>
      `).join('');

      // Xử lý click vào kết quả
      searchResults.querySelectorAll('.search-result-item').forEach(item => {
        item.addEventListener('click', function() {
          const stt = parseInt(this.dataset.stt, 10);
          const foundIdx = grid.findIndex(g => g == stt);
          if (foundIdx >= 0) {
            show(foundIdx);
            if (searchInput) searchInput.value = '';
            searchResults.style.display = 'none';
            // Scroll đến câu trong grid
            const gridItem = gridEl.querySelector(`[data-i="${foundIdx}"]`);
            if (gridItem) {
              gridItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }
        });
      });

      searchResults.style.display = 'block';
    } catch (e) {
      console.error('Search error:', e);
      if (searchResults) {
        searchResults.innerHTML = '<div class="search-no-results">Lỗi tìm kiếm</div>';
        searchResults.style.display = 'block';
      }
    }
  }

  // Tìm kiếm khi gõ (debounce)
  if (searchInput) {
    searchInput.addEventListener('input', function(e) {
      const query = e.target.value.trim();
      
      clearTimeout(searchTimeout);
      
      if (query.length === 0) {
        if (searchResults) searchResults.style.display = 'none';
        return;
      }

      // Nếu là số thuần túy và hợp lệ, tìm ngay
      if (/^\d+$/.test(query)) {
        const num = parseInt(query, 10);
        if (num >= 1 && num <= 600) {
          const foundIdx = grid.findIndex(g => g == num);
          if (foundIdx >= 0) {
            show(foundIdx);
            if (searchResults) searchResults.style.display = 'none';
            const gridItem = gridEl.querySelector(`[data-i="${foundIdx}"]`);
            if (gridItem) {
              gridItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
          }
        }
      }

      // Debounce cho tìm kiếm từ khóa
      searchTimeout = setTimeout(() => {
        performSearch(query);
      }, 300);
    });

    // Xử lý Enter
    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const query = this.value.trim();
        
        if (/^\d+$/.test(query)) {
          const num = parseInt(query, 10);
          if (num >= 1 && num <= 600) {
            const foundIdx = grid.findIndex(g => g == num);
            if (foundIdx >= 0) {
              show(foundIdx);
              if (searchResults) searchResults.style.display = 'none';
              const gridItem = gridEl.querySelector(`[data-i="${foundIdx}"]`);
              if (gridItem) {
                gridItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
              }
            }
          }
        } else if (query.length > 0 && searchResults) {
          // Nếu có kết quả, chọn kết quả đầu tiên
          const firstResult = searchResults.querySelector('.search-result-item');
          if (firstResult) {
            firstResult.click();
          }
        }
      } else if (e.key === 'Escape') {
        if (searchResults) searchResults.style.display = 'none';
        this.blur();
      }
    });

    // Ẩn kết quả khi click ra ngoài
    document.addEventListener('click', function(e) {
      if (searchInput && searchResults && 
          !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
      }
    });
  }
});
