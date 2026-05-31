// ========================================
// 功能：修改密码
// 挂载点：#password-app
// 数据流：sdk.users.updatePassword()
// ========================================

(function () {
  var el = document.getElementById('password-app');
  if (!el || !window.Vue) return;

  Vue.createApp({
    setup: function () {
      var newPassword = Vue.ref('');
      var confirmPassword = Vue.ref('');
      var saving = Vue.ref(false);
      var msg = Vue.ref('');

      Vue.onMounted(function () { if (!requireAuth()) return; });

      function save() {
        if (newPassword.value !== confirmPassword.value) { msg.value = '两次密码不一致'; return; }
        if (newPassword.value.length < 6) { msg.value = '密码至少6位'; return; }
        saving.value = true;
        msg.value = '';
        sdk.users.updatePassword({ password: newPassword.value })
          .then(function () { msg.value = '密码修改成功'; newPassword.value = ''; confirmPassword.value = ''; })
          .catch(function (e) { msg.value = e.message || '修改失败'; })
          .finally(function () { saving.value = false; });
      }

      return { newPassword: newPassword, confirmPassword: confirmPassword, saving: saving, msg: msg, save: save };
    },
    template: '<div>' +
      '<input v-model="newPassword" type="password" placeholder="新密码" />' +
      '<input v-model="confirmPassword" type="password" placeholder="确认新密码" />' +
      '<p v-if="msg" :style="{color:msg.includes(\'成功\')?\'green\':\'red\'}">{{ msg }}</p>' +
      '<button @click="save" :disabled="saving">{{ saving ? \'修改中...\' : \'修改密码\' }}</button>' +
      '<p><a href="/user">← 返回个人中心</a></p></div>'
  }).mount(el);
})();
