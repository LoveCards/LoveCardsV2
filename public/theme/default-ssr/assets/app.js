// ========================================
// 入口文件：初始化 SDK + 全局工具 + 路由分发 + 主题切换
// 依赖：axios CDN → lovecards.umd.js → 本文件
// ========================================

(function () {
  var token = localStorage.getItem('token');

  // --- Toast 组件 ---
  window.showToast = function (msg, type) {
    var el = document.getElementById('toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'toast';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.className = 'toast ' + (type || '');
    el.style.display = 'block';
    setTimeout(function () { el.style.display = 'none'; }, 3000);
  };

  // --- 认证工具 ---
  window.goLogin = function () { location.href = '/login'; };

  window.requireAuth = function () {
    if (!localStorage.getItem('token')) { window.goLogin(); return false; }
    return true;
  };

  window.getTokenUserId = function () {
    var t = localStorage.getItem('token');
    if (!t) return null;
    try {
      var payload = JSON.parse(atob(t.split('.')[1]));
      return (payload.data && payload.data.uid) || null;
    } catch (e) { return null; }
  };

  // --- SDK 初始化 ---
  window.sdk = window.LC.createClient({
    apiUrl: '/api',
    onAuthError: function () {
      localStorage.removeItem('token');
      window.showToast('登录已过期，请重新登录', 'error');
      setTimeout(window.goLogin, 1500);
    },
    onError: function (err) {
      window.showToast(err.message, 'error');
    }
  });

  if (token) window.sdk.setToken(token);

  // --- 导航用户状态更新 ---
  function updateAuthUI() {
    var hasToken = !!localStorage.getItem('token');
    var loginLink = document.getElementById('login-link');
    var registerLink = document.getElementById('register-link');
    var userLink = document.getElementById('user-link');
    var fab = document.getElementById('fab-publish');

    if (loginLink) loginLink.style.display = hasToken ? 'none' : '';
    if (registerLink) registerLink.style.display = hasToken ? 'none' : '';
    if (userLink) userLink.style.display = hasToken ? '' : 'none';
    if (fab) {
      fab.style.display = window.innerWidth < 768 && hasToken ? 'flex' : 'none';
      // 桌面端始终显示发布按钮（在侧边栏里）
    }
  }
  updateAuthUI();
  window.addEventListener('resize', updateAuthUI);

  // --- auth-required 链接拦截 ---
  document.addEventListener('click', function (e) {
    var link = e.target.closest('.auth-required');
    if (link && !localStorage.getItem('token')) {
      e.preventDefault();
      window.goLogin();
    }
  });

  // --- 暗色模式切换 ---
  function initThemeToggle() {
    var toggles = document.querySelectorAll('#theme-toggle-desktop, #theme-toggle-mobile');
    toggles.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
      });
    });
  }
  initThemeToggle();

  // --- FAB 显示逻辑 ---
  if (window.innerWidth >= 768) {
    var fab = document.getElementById('fab-publish');
    if (fab) fab.style.display = 'none';
  }
})();
