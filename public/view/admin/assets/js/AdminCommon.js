class AdminCommon extends Base {

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
    // SetHooksPostLogout = (hooks, defultStatus = false, thisHooksKey = 'PostLogout') => {
    //     //设置方法
    //     this.SetRequestApiUrl(hooks, defultStatus, thisHooksKey);
    // }

    //内部
    PostLogout = () => {
        var data = {};
        this.RequestApiUrl('post', 'AuthLogout', {
            then: () => {
                this.DeleteToken('AdminTokenName');
                this.commonFunctions.snackbar('退出成功');
                location.reload();
            }
        }, data);
    }
}