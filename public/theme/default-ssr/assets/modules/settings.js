// ========================================
// 功能：编辑资料
// 挂载点：#settings-app
// 数据流：sdk.users.me() / sdk.users.updateMe()
// ========================================

(function () {
  var el = document.getElementById('settings-app');
  if (!el || !window.Vue) return;

  Vue.createApp({
    setup: function () {
      var username = Vue.ref('');
      var email = Vue.ref('');
      var loading = Vue.ref(true);
      var saving = Vue.ref(false);
      var msg = Vue.ref('');

      Vue.onMounted(function () {
        if (!requireAuth()) return;
        sdk.users.me()
          .then(function (res) { username.value = res.data.username || ''; email.value = res.data.email || ''; })
          .catch(function () { goLogin(); })
          .finally(function () { loading.value = false; });
      });

      function save() {
        saving.value = true;
        msg.value = '';
        sdk.users.updateMe({ username: username.value })
          .then(function () { msg.value = '保存成功'; })
          .catch(function (e) { msg.value = e.message || '保存失败'; })
          .finally(function () { saving.value = false; });
      }

      return { username: username, email: email, loading: loading, saving: saving, msg: msg, save: save };
    },
    template: '<div>' +
      '<p v-if="loading">加载中...</p>' +
      '<div v-else>' +
      '<input v-model="username" placeholder="用户名" />' +
      '<input :value="email" disabled placeholder="邮箱（不可修改）" />' +
      '<p v-if="msg" :style="{color:msg.includes(\'成功\')?\'green\':\'red\'}">{{ msg }}</p>' +
      '<button @click="save" :disabled="saving">{{ saving ? \'保存中...\' : \'保存\' }}</button>' +
      '</div></div>'
  }).mount(el);
})();
