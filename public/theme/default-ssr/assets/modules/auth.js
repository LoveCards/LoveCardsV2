// ========================================
// 功能：登录 / 注册 / 访客
// 挂载点：#login-app
// 数据流：sdk.session.login() / register() / guest()
// ========================================

(function () {
  var el = document.getElementById('login-app');
  if (!el || !window.Vue) return;

  Vue.createApp({
    setup: function () {
      var mode = Vue.ref('login');
      var account = Vue.ref('');
      var password = Vue.ref('');
      var passwordConfirm = Vue.ref('');
      var loading = Vue.ref(false);
      var error = Vue.ref('');

      function submit() {
        error.value = '';
        loading.value = true;

        if (mode.value === 'login') {
          sdk.session.login({ account: account.value, password: password.value })
            .then(function (res) {
              var d = res.data;
              localStorage.setItem('token', d.token);
              sdk.setToken(d.token);
              location.href = '/user';
            })
            .catch(function (e) { error.value = e.message; })
            .finally(function () { loading.value = false; });
        } else {
          if (password.value !== passwordConfirm.value) {
            error.value = '两次密码不一致';
            loading.value = false;
            return;
          }
          sdk.session.register({
            account: account.value,
            password: password.value,
            password_confirm: passwordConfirm.value
          })
            .then(function (res) {
              var d = res.data;
              localStorage.setItem('token', d.token);
              sdk.setToken(d.token);
              location.href = '/user';
            })
            .catch(function (e) { error.value = e.message; })
            .finally(function () { loading.value = false; });
        }
      }

      function guestLogin() {
        sdk.session.guest()
          .then(function (res) {
            var d = res.data;
            localStorage.setItem('token', d.token);
            sdk.setToken(d.token);
            location.href = '/';
          });
      }

      function switchMode() {
        mode.value = mode.value === 'login' ? 'register' : 'login';
        error.value = '';
      }

      return {
        mode: mode, account: account, password: password,
        passwordConfirm: passwordConfirm, loading: loading, error: error,
        submit: submit, guestLogin: guestLogin, switchMode: switchMode
      };
    },
    template: '<article>' +
      '<header><h3>{{ mode === \'login\' ? \'登录\' : \'注册\' }}</h3></header>' +
      '<p v-if="error" style="color:red">{{ error }}</p>' +
      '<input v-model="account" placeholder="账号（邮箱/手机号/用户名）" />' +
      '<input v-model="password" type="password" placeholder="密码" />' +
      '<input v-if="mode===\'register\'" v-model="passwordConfirm" type="password" placeholder="确认密码" />' +
      '<button @click="submit" :disabled="loading">{{ loading ? \'处理中...\' : (mode === \'login\' ? \'登录\' : \'注册\') }}</button>' +
      '<footer style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem">' +
      '<a href="#" @click.prevent="switchMode">{{ mode === \'login\' ? \'没有账号？注册\' : \'已有账号？登录\' }}</a>' +
      '<button @click="guestLogin" class="secondary outline" style="margin:0">访客进入</button></footer></article>'
  }).mount(el);
})();
