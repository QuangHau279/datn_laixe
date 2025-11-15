// ===== Stats: random -> count-up -> stop (clean & forced) =====
(function() {
  'use strict';
  
  let hasAnimated = false;
  
  function formatNumber(n, el) {
    const pad = el.dataset.pad;
    const suffix = el.dataset.suffix || '';
    let s = String(Math.round(n));
    if (pad) s = s.padStart(parseInt(pad, 10), '0');
    return s + suffix;
  }

  function animateStat(el) {
    if (el.dataset.done === '1') return;
    el.dataset.done = '1';

    const target = parseFloat(el.dataset.target || '0');
    const prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    if (prefersReduce) { 
      el.textContent = formatNumber(target, el); 
      return; 
    }

    // 1) Scramble nhanh (random số) - 600ms
    const SCR_MS = 600;
    const STEP = 30;
    const MAX = Math.max(1, Math.floor(target * 1.3));
    
    const scr = setInterval(() => { 
      el.textContent = formatNumber(Math.floor(Math.random() * MAX), el); 
    }, STEP);

    // 2) Đếm mượt tới đúng số - 900ms
    setTimeout(() => {
      clearInterval(scr);
      const DUR = 900;
      const t0 = performance.now();
      const ease = t => 1 - Math.pow(1 - t, 3);
      
      function tick(now) {
        const p = Math.min(1, (now - t0) / DUR);
        el.textContent = formatNumber(target * ease(p), el);
        if (p < 1) {
          requestAnimationFrame(tick);
        } else {
          el.textContent = formatNumber(target, el);
        }
      }
      tick(t0);
    }, SCR_MS);
  }

  function startAnimation() {
    if (hasAnimated) return;
    hasAnimated = true;
    
    const items = document.querySelectorAll('.js-stat');
    items.forEach(animateStat);
  }

  function initStats() {
    const section = document.getElementById('stats');
    if (!section) {
      console.warn('[stats] Section #stats not found');
      return;
    }
    
    const items = document.querySelectorAll('.js-stat');
    if (items.length === 0) {
      console.warn('[stats] No .js-stat elements found');
      return;
    }
    
    console.log('[stats] Found', items.length, 'stat elements');

    // Kiểm tra xem section có trong view không
    function checkInView() {
      const rect = section.getBoundingClientRect();
      const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
      return rect.top < viewportHeight && rect.bottom > 0;
    }

    // Sử dụng IntersectionObserver nếu có
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            console.log('[stats] Section in view, starting animation');
            startAnimation();
            observer.disconnect();
          }
        });
      }, {
        threshold: 0.1,
        rootMargin: '0px'
      });
      
      observer.observe(section);
      console.log('[stats] IntersectionObserver initialized');
    } else {
      // Fallback: dùng scroll event
      let triggered = false;
      function handleScroll() {
        if (!triggered && checkInView()) {
          triggered = true;
          console.log('[stats] Scrolled to section, starting animation');
          startAnimation();
          window.removeEventListener('scroll', handleScroll);
          window.removeEventListener('resize', handleScroll);
        }
      }
      
      window.addEventListener('scroll', handleScroll, { passive: true });
      window.addEventListener('resize', handleScroll);
      
      // Kiểm tra ngay nếu đã trong view
      if (checkInView()) {
        setTimeout(handleScroll, 100);
      }
    }

    // Helper function để test
    window.__runStats = function() {
      hasAnimated = false;
      document.querySelectorAll('.js-stat').forEach(el => {
        el.dataset.done = '';
      });
      startAnimation();
    };
  }

  // Khởi tạo khi DOM sẵn sàng
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStats);
  } else {
    setTimeout(initStats, 100);
  }
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

        // Xử lý lỗi 429 (quá giới hạn) trước khi parse JSON
        if (response.status === 429) {
          try {
            const errorData = await response.json();
            const errorMsg = errorData?.message || 'Đã đạt giới hạn số câu hỏi';
            const suggestion = errorData?.suggestion || '';
            addMessage(`⚠️ ${errorMsg}\n\n${suggestion}`, 'ai');
            return;
          } catch (e) {
            // Nếu không parse được JSON, dùng text
            const errorText = await response.text();
            addMessage(`⚠️ Đã đạt giới hạn số câu hỏi. ${errorText}`, 'ai');
            return;
          }
        }
        
        if (!response.ok) {
          const errorText = await response.text();
          console.error('[Chatbox] Server error:', response.status, errorText);
          throw new Error(`Server error: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();
        console.log('[Chatbox] Response data:', data);
        
        const answer = data?.answer || 'Xin lỗi, tôi không hiểu câu hỏi này.';
        
        // Hiển thị số câu còn lại nếu có
        let displayAnswer = answer;
        if (data?.remaining !== undefined && data?.limit !== undefined) {
          const remaining = data.remaining;
          const limit = data.limit;
          if (remaining <= 3 && remaining > 0) {
            displayAnswer += `\n\n⚠️ Bạn còn ${remaining}/${limit} câu hỏi trong session này.`;
          } else if (remaining === 0) {
            displayAnswer += `\n\n⚠️ Bạn đã sử dụng hết ${limit} câu hỏi trong session này.`;
          }
        }

        addMessage(displayAnswer, 'ai');
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

// ===== Toast Notification =====
function showToast(message, type = 'success') {
  // Remove existing toast
  const existingToast = document.querySelector('.toast');
  if (existingToast) {
    existingToast.remove();
  }
  
  // Create toast element
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  const icon = type === 'success' ? '✅' : '❌';
  toast.innerHTML = `
    <span class="toast-icon">${icon}</span>
    <span class="toast-message">${message}</span>
    <button class="toast-close" onclick="this.parentElement.remove()">×</button>
  `;
  
  document.body.appendChild(toast);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (toast.parentElement) {
      toast.style.animation = 'toastSlideIn 0.3s ease reverse';
      setTimeout(() => toast.remove(), 300);
    }
  }, 5000);
}

// ===== Lead Form Submission =====
(function() {
  'use strict';
  
  function initLeadForm() {
    const form = document.getElementById('leadForm');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (!form || !btnSubmit) {
      console.warn('[Lead Form] Form or button not found');
      return;
    }
    
    console.log('[Lead Form] Initialized');
    
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!CSRF_TOKEN) {
      console.error('[Lead Form] CSRF token not found');
    }
    
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      console.log('[Lead Form] Form submitted');
      
      // Disable button
      btnSubmit.disabled = true;
      const originalText = btnSubmit.textContent;
      btnSubmit.textContent = 'Đang xử lý...';
      
      // Get form data
      const formData = new FormData(form);
      const data = {
        name: formData.get('name')?.trim(),
        phone: formData.get('phone')?.trim(),
        license: formData.get('license') || null,
      };
      
      console.log('[Lead Form] Submitting data:', data);
      
      // Validate
      if (!data.name || !data.phone) {
        showToast('Vui lòng điền đầy đủ thông tin!', 'error');
        btnSubmit.disabled = false;
        btnSubmit.textContent = originalText;
        return;
      }
      
      try {
        const response = await fetch('/api/leads', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(data),
        });
        
        console.log('[Lead Form] Response status:', response.status);
        
        const result = await response.json();
        console.log('[Lead Form] Response data:', result);
        
        if (result.success) {
          // Success: show toast and reset form
          showToast(result.message || 'Đăng ký thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.', 'success');
          form.reset();
        } else {
          // Error: show error message
          const errorMsg = result.message || result.errors ? Object.values(result.errors || {}).flat().join(', ') : 'Có lỗi xảy ra. Vui lòng thử lại.';
          showToast(errorMsg, 'error');
        }
      } catch (error) {
        console.error('[Lead Form] Error:', error);
        showToast('Có lỗi kết nối. Vui lòng thử lại sau.', 'error');
      } finally {
        // Re-enable button
        btnSubmit.disabled = false;
        btnSubmit.textContent = originalText;
      }
    });
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLeadForm);
  } else {
    initLeadForm();
  }
})();