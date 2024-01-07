const { createApp } = Vue;
import router from './router/router.js';

const BaseEntity = new Base();
const CommonEntity = new Common();

//通用跳转
const JumpUrl = (url, time) => CommonEntity.JumpUrl(url, time);

const app = createApp({
    created() {
        //CookieMsg处理
        CommonEntity.CookieMsgHandling();
        //内置类绑定注销
        CommonEntity.BindLogout('Logout');
    }
});
app.use(router);
app.mount('#app');
