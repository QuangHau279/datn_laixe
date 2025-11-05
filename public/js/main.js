// ===== Stats: random -> count-up -> stop (clean & forced) =====
(() => {
  const section  = document.getElementById('stats');
  const items    = Array.from(document.querySelectorAll('.js-stat'));
  const FORCE    = false; // production default: respect user motion preference
  console.log('[stats] items:', items.length);
  if (!items.length) return;

  const prefersReduce = !FORCE && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const format = (n, el) => {
    const pad = el.dataset.pad;
    const suffix = el.dataset.suffix || '';
    let s = String(Math.round(n));
    if (pad) s = s.padStart(parseInt(pad, 10), '0');
    return s + suffix;
  };

  function animate(el) {
    if (el.dataset.done === '1') return;
    el.dataset.done = '1';

    const target = parseFloat(el.dataset.target || '0');
    if (prefersReduce) { el.textContent = format(target, el); return; }

    // 1) Scramble nhanh
    const SCR_MS = 600, STEP = 30, MAX = Math.max(1, Math.floor(target * 1.3));
    const scr = setInterval(() => { el.textContent = format(Math.floor(Math.random() * MAX), el); }, STEP);

    // 2) Đếm mượt tới đúng số
    setTimeout(() => {
      clearInterval(scr);
      const DUR = 900, t0 = performance.now();
      const ease = t => 1 - Math.pow(1 - t, 3);
      (function tick(now){
        const p = Math.min(1, (now - t0) / DUR);
        el.textContent = format(target * ease(p), el);
        if (p < 1) requestAnimationFrame(tick);
      })(t0);
    }, SCR_MS);
  }

  const start = () => { console.log('[stats] start'); items.forEach(animate); };

  // Kích hoạt: IO nếu có, fallback scroll, và đảm bảo tự chạy sau 1.2s
  const inView = () => {
    if (!section) return true;
    const r = section.getBoundingClientRect();
    return r.top < window.innerHeight * 0.7 && r.bottom > 0;
  };

  if ('IntersectionObserver' in window && section) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { start(); io.disconnect(); } });
    }, { threshold: 0.25 });
    io.observe(section);
  } else {
    if (inView()) start();
    else {
      const onScroll = () => { if (inView()) { start(); window.removeEventListener('scroll', onScroll); window.removeEventListener('resize', onScroll); } };
      window.addEventListener('scroll', onScroll, { passive:true });
      window.addEventListener('resize', onScroll);
      setTimeout(start, 1200);
    }
  }

  // Dev helper: chạy lại bằng console
  window.__runStats = () => items.forEach(el => { el.dataset.done=''; animate(el); });
})();

/* ===== Off-canvas menu: safe init ===== */
(() => {
  const q = (id) => document.getElementById(id);
  const btnMenu = q('btnMenu');
  const btnClose = q('btnCloseMenu');
  const menu = q('menuRight');
  const scrim = q('scrim');

  if (!menu || !btnMenu) {
    console.warn('[menu] thiếu phần tử: menuRight/btnMenu');
    return;
  }

  const open = () => {
    menu.classList.add('open');
    menu.setAttribute('aria-hidden','false');
    btnMenu.setAttribute('aria-expanded','true');
    scrim?.classList.add('show');
  };
  const close = () => {
    menu.classList.remove('open');
    menu.setAttribute('aria-hidden','true');
    btnMenu.setAttribute('aria-expanded','false');
    scrim?.classList.remove('show');
  };

  // gắn listener
  btnMenu.addEventListener('click', open);
  btnClose?.addEventListener('click', close);
  scrim?.addEventListener('click', close);
  window.addEventListener('keydown', e => e.key === 'Escape' && close());

  // tiện test
  window.__menuTest = { open, close };
  console.log('[menu] ready');
})();

// ===== Generic reveal on scroll =====
(() => {
  const els = Array.from(document.querySelectorAll('.reveal'));
  if (!els.length) return;
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce){ els.forEach(el => el.classList.add('visible')); return; }

  const io = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => {
        entries.forEach(e => {
          if (e.isIntersecting){
            e.target.classList.add('visible');
            io.unobserve(e.target);
          }
        });
      }, { threshold:.15 })
    : null;

  els.forEach(el => {
    if (io) io.observe(el); else el.classList.add('visible');
  });
})();

// ===== Chatbox Widget =====
(() => {
  // Lấy API URL từ window.chatApiUrl hoặc default
  const getApiUrl = () => {
    const url = window.chatApiUrl;
    if (url && url !== 'null' && url !== 'undefined' && url.trim() !== '') {
      return url;
    }
    return 'http://localhost:7070/chat';
  };
  
  const API_URL = getApiUrl();
  console.log('[Chatbox] API URL:', API_URL);
  
  let messages = [];
  let isOpen = false;

  // Khởi tạo chatbox HTML nếu chưa có
  function initChatbox() {
    if (document.getElementById('chatbox-window')) return;

    const chatboxHTML = `
      <button class="chatbox-toggle" id="chatbox-toggle" aria-label="Mở chatbox">
        <svg viewBox="0 0 24 24">
          <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
          <path d="M7 9h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"/>
        </svg>
      </button>
      
      <div class="chatbox-window" id="chatbox-window" role="dialog" aria-hidden="true">
        <div class="chatbox-header">
          <div class="chatbox-header-title">Trợ lý AI Lý thuyết lái xe</div>
          <button class="chatbox-close" id="chatbox-close" aria-label="Đóng chatbox"></button>
        </div>
        
        <div class="chatbox-messages" id="chatbox-messages" role="log" aria-live="polite">
          <div class="chatbox-message ai">
            <div class="chatbox-message-avatar">🤖</div>
            <div class="chatbox-message-content">
              Xin chào! Tôi là trợ lý AI về lý thuyết lái xe và luật giao thông Việt Nam. Bạn cần hỏi gì?
            </div>
          </div>
        </div>
        
        <div class="chatbox-input-area">
          <textarea 
            class="chatbox-input" 
            id="chatbox-input" 
            placeholder="Nhập câu hỏi của bạn..." 
            rows="1"
            aria-label="Nhập tin nhắn"
          ></textarea>
          <button class="chatbox-send" id="chatbox-send" aria-label="Gửi tin nhắn">
            <svg viewBox="0 0 24 24">
              <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
          </button>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', chatboxHTML);

    // Gắn event listeners
    const toggleBtn = document.getElementById('chatbox-toggle');
    const closeBtn = document.getElementById('chatbox-close');
    const windowEl = document.getElementById('chatbox-window');
    const messagesEl = document.getElementById('chatbox-messages');
    const inputEl = document.getElementById('chatbox-input');
    const sendBtn = document.getElementById('chatbox-send');

    // Auto-resize textarea
    inputEl.addEventListener('input', () => {
      inputEl.style.height = 'auto';
      inputEl.style.height = inputEl.scrollHeight + 'px';
    });

    // Toggle chatbox
    toggleBtn.addEventListener('click', () => {
      isOpen = !isOpen;
      windowEl.classList.toggle('open');
      windowEl.setAttribute('aria-hidden', !isOpen);
      if (isOpen) {
        inputEl.focus();
      }
    });

    closeBtn.addEventListener('click', () => {
      isOpen = false;
      windowEl.classList.remove('open');
      windowEl.setAttribute('aria-hidden', 'true');
    });

    // Send message
    const sendMessage = async () => {
      const message = inputEl.value.trim();
      if (!message || sendBtn.disabled) return;

      // Disable input
      sendBtn.disabled = true;
      inputEl.disabled = true;

      // Add user message
      addMessage(message, 'user');
      inputEl.value = '';
      inputEl.style.height = 'auto';

      // Show typing indicator
      const typingId = addTypingIndicator();

      try {
        console.log('[Chatbox] Sending message to:', API_URL);
        const response = await fetch(API_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message })
        });

        removeTypingIndicator(typingId);

        console.log('[Chatbox] Response status:', response.status, response.statusText);

        if (!response.ok) {
          const errorText = await response.text();
          console.error('[Chatbox] Server error:', response.status, errorText);
          throw new Error(`Server error: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();
        console.log('[Chatbox] Response data:', data);
        
        const answer = data?.answer || 'Xin lỗi, tôi không hiểu câu hỏi này.';

        addMessage(answer, 'ai');
      } catch (error) {
        removeTypingIndicator(typingId);
        console.error('[Chatbox] Error details:', error);
        
        let errorMessage = 'Xin lỗi, có lỗi xảy ra khi kết nối với server. ';
        if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
          errorMessage += 'Vui lòng kiểm tra xem chatbox API server đã chạy chưa (http://localhost:7070).';
        } else if (error.message.includes('CORS')) {
          errorMessage += 'Lỗi CORS. Vui lòng kiểm tra cấu hình server.';
        } else {
          errorMessage += error.message || 'Vui lòng thử lại sau.';
        }
        
        addMessage(errorMessage, 'ai');
      } finally {
        sendBtn.disabled = false;
        inputEl.disabled = false;
        inputEl.focus();
      }
    };

    sendBtn.addEventListener('click', sendMessage);
    inputEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    // Helper functions
    function addMessage(content, type) {
      const time = new Date().toLocaleTimeString('vi-VN', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
      
      const messageEl = document.createElement('div');
      messageEl.className = `chatbox-message ${type}`;
      messageEl.innerHTML = `
        <div class="chatbox-message-avatar">${type === 'user' ? 'Bạn' : '🤖'}</div>
        <div class="chatbox-message-content">
          ${content.replace(/\n/g, '<br>')}
          <div class="chatbox-message-time">${time}</div>
        </div>
      `;
      
      messagesEl.appendChild(messageEl);
      scrollToBottom();
    }

    function addTypingIndicator() {
      const typingEl = document.createElement('div');
      typingEl.className = 'chatbox-typing';
      typingEl.innerHTML = '<span></span><span></span><span></span>';
      typingEl.dataset.id = Date.now();
      messagesEl.appendChild(typingEl);
      scrollToBottom();
      return typingEl.dataset.id;
    }

    function removeTypingIndicator(id) {
      const typingEl = messagesEl.querySelector(`.chatbox-typing[data-id="${id}"]`);
      if (typingEl) typingEl.remove();
    }

    function scrollToBottom() {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    // Thêm message welcome vào history
    messages.push({ type: 'ai', content: 'Xin chào! Tôi là trợ lý AI về lý thuyết lái xe và luật giao thông Việt Nam. Bạn cần hỏi gì?' });
  }

  // Khởi tạo khi DOM sẵn sàng
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChatbox);
  } else {
    initChatbox();
  }
})();