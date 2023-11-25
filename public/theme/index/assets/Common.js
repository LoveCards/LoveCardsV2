class Common extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    //外部
    //Cookie传输的提示处理
    CookieMsgHandling = () => {
        const msg = $.cookie('msg');
        //判断
        if (msg != 'undefined' && msg != undefined) {
            //弹出并
            this.commonFunctions.snackbar(msg);
            $.cookie('msg', 'undefined', { path: '/' });
        }
        return;
    }

    //退出账户
    BindLogout = (submitId) => {
        //绑定方法
        $('#' + submitId).click(function () { this.PostLogout() }.bind(this));
    }
    SetPostLogoutHooks = (intiHook, thanHook, catchJHook) => {
        //设置方法
        this.hooks.PostLogout = {};
        this.hooks.PostLogout.inti = intiHook;
        this.hooks.PostLogout.then = thanHook;
        this.hooks.PostLogout.catch = catchJHook;
    }

    //内部
    PostLogout = () => {
        if (this.hooks.PostLogout?.inti) {
            //自定义回调函数
            this.hooks.PostLogout.inti();
        } else {
            this.commonFunctions.snackbar('正在发起请求！');
        }

        var data = {};

        //提交数据
        return this.Axios('post', this.config.apiUrl.AuthLogout, data).then((response) => {
            // this.hooks.PostLogout?.then写法解释
            // this.hooks.PostLogout 存在并且包含属性 then
            // 这将在 this.hooks.PostLogout 存在并且 then 存在的情况下执行操作
            if (this.hooks.PostLogout?.then) {
                //自定义回调函数
                this.hooks.PostLogout.then();
            } else {
                //默认回调函数
                this.DeleteAdminToken();//清除Token
                this.commonFunctions.snackbar('退出成功，正在跳转');
            }
        }).catch((error) => {
            if (this.hooks.PostLogout?.catch) {
                this.hooks.PostLogout.catch();
            } else {
                this.AxiosErrorHandling(error);
            }
        });
    }

}