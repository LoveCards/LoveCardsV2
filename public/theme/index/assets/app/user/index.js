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
    },
    methods: {
        postLogout() {
            BaseEntity.RequestApiUrl('post', 'UserAuthLogout', {
                inti: () => { },
                then: (response) => {
                    BaseEntity.DeleteToken('UserTokenName');//清除Token
                    BaseEntity.commonFunctions.snackbar('退出成功，正在跳转');
                    window.location.replace('/');
                }
            }, [], 'UserTokenName');
        }
    }
});
app.use(router);
app.mount('#app');
