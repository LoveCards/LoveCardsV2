class Login extends LoveCards {

    constructor() {
        super();
        this.hooks = {};
    }

    //外部
    BindLogin = (submitId, usernameId, passwordId) => {
        //绑定方法
        if (this.config.geetest4.CaptchaStatus) {
            this.Geetest4(submitId, (CaptchaData) => this.PostLogin(usernameId, passwordId, CaptchaData));
        } else {
            $('#' + submitId).click(function () { this.PostLogin(usernameId, passwordId) }.bind(this));
        }
    }
    SetPostLoginHooks = (intiHook, thanHook, catchJHook) => {
        //设置方法
        this.hooks.PostLogin = {};
        this.hooks.PostLogin.inti = intiHook;
        this.hooks.PostLogin.then = thanHook;
        this.hooks.PostLogin.catch = catchJHook;
    }

    //内部
    PostLogin = (usernameId, passwordId, CaptchaData) => {
        if (this.hooks.PostLogin?.inti) {
            //自定义回调函数
            this.hooks.PostLogin.inti();
        } else {
            this.commonFunctions.snackbar('正在发起请求！');
        }

        //是否存在验证参数
        var data;
        if (CaptchaData) { data = CaptchaData; }

        var data = {
            ...data,//合并验证参数
            'userName': $('#' + usernameId).val(),
            'password': $('#' + passwordId).val(),
        };

        //提交数据
        return this.Axios('post', this.config.apiUrl.AuthLogin, data).then((response) => {
            // this.hooks.PostLogin?.then写法解释
            // this.hooks.PostLogin 存在并且包含属性 then
            // 这将在 this.hooks.PostLogin 存在并且 then 存在的情况下执行操作
            if (this.hooks.PostLogin?.then) {
                //自定义回调函数
                this.hooks.PostLogin.then();
            } else {
                //默认回调函数
                this.SetAdminToken(response.data.token);//设置Token
                this.commonFunctions.snackbar('登入成功，正在跳转');
            }
        }).catch((error) => {
            if (this.hooks.PostLogin?.catch) {
                this.hooks.PostLogin.catch();
            } else {
                this.AxiosErrorHandling(error);
            }
        });
    }
}