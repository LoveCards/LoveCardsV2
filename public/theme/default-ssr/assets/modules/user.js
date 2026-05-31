// ========================================
// 功能：用户中心
// 挂载点：#user-app
// 数据流：sdk.users.me() / sdk.session.logout()
// ========================================

(function () {
  var el = document.getElementById('user-app');
  if (!el || !window.Vue) return;

  Vue.createApp({
    setup: function () {
      var user = Vue.ref(null);
      var loading = Vue.ref(true);

      Vue.onMounted(function () {
        if (!requireAuth()) return;
        sdk.users.me()
          .then(function (res) { user.value = res.data; })
          .catch(function () { localStorage.removeItem('token'); goLogin(); })
          .finally(function () { loading.value = false; });
      });

      function logout() {
        sdk.session.logout().catch(function () {});
        localStorage.removeItem('token');
        location.href = '/';
      }

      return { user: user, loading: loading, logout: logout };
    },
    template: '<div>' +
      '<p v-if="loading">加载中...</p>' +
      '<div v-else-if="user">' +
      '<article><header><h3>{{ user.username || \'用户\' }}</h3></header>' +
      '<p>邮箱: {{ user.email || \'未设置\' }}</p>' +
      '<p>注册时间: {{ user.created_at }}</p></article>' +
      '<nav style="margin-top:1.5rem"><ul style="list:none;padding:0;display:flex;flex-wrap:wrap;gap:0.5rem">' +
      '<li><a href="/publish" role="button">发布卡片</a></li>' +
      '<li><a href="/user/cards" role="button" class="secondary outline">我的卡片</a></li>' +
      '<li><a href="/user/comments" role="button" class="secondary outline">我的评论</a></li>' +
      '<li><a href="/user/likes" role="button" class="secondary outline">我的点赞</a></li>' +
      '<li><a href="/user/settings" role="button" class="secondary outline">编辑资料</a></li>' +
      '<li><a href="/user/password" role="button" class="secondary outline">修改密码</a></li>' +
      '</ul></nav>' +
      '<button @click="logout" class="secondary outline" style="margin-top:1rem">退出登录</button>' +
      '</div></div>'
  }).mount(el);
})();
