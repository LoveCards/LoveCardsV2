const { createApp } = Vue;
import router from './router/router.js';

const BaseEntity = new Base();
const CommonEntity = new Common();

//通用跳转
const JumpUrl = (url, time) => CommonEntity.JumpUrl(url, time);

const app = createApp({
    data() {
        return {
            theme: {}
        }
    },
    created() {
        //CookieMsg处理
        CommonEntity.CookieMsgHandling();
    },
    methods: {
        getThemeConfig() {
            CommonEntity.RequestApiUrl('get', 'ThemeConfig', undefined, [], 'UserTokenName').then((req) => {
                this.theme = req.data;
            }).catch((err) => {
                BaseEntity.commonFunctions.snackbar('主题配置获取失败，请刷新页面后再试！');
            })
        },
        postLogout() {
            BaseEntity.RequestApiUrl('post', 'UserAuthLogout', {
                inti: () => { },
                then: (response) => {
                    BaseEntity.DeleteToken('UserTokenName');//清除Token
                    BaseEntity.commonFunctions.snackbar('退出成功，正在跳转');
                    window.location.replace('/');
                }
            }, [], 'UserTokenName');
        },
        // FunSetBtnThemeDarkValue() {
        //     //根据Cookie初始化主题
        //     var cookieValue = $.cookie("ThemeDark");
        //     if (cookieValue == undefined || cookieValue == "") {
        //         cookieValue = theme.Config.ThemeDark;
        //         $.cookie("ThemeDark", cookieValue, { expires: 7, path: "/" });
        //     }
        //     if (cookieValue == "true") {
        //         $("#jsBtnThemeDark").attr("value", "true");
        //         FunSetThemeDarkStyle();
        //     } else {
        //         $("#jsBtnThemeDark").attr("value", "false");
        //         FunSetThemeDarkStyle();
        //     }
        // }
    }
});
app.use(router);
app.mount('#app');
