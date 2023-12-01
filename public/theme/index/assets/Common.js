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

    /**
     * Class绑定点赞
     * 用法：该元素value值应为点赞请求ID
     * @param {String} submitClass 
     * @param {Function} newHtml 传入匿名函数，第一个默认参数将返回点赞更新变量
     */
    CBindCardsGood = (submitClass, newHtml) => {
        //导入this
        const self = this;

        //绑定方法
        $('.' + submitClass).click(function () {
            const submit = $(this);
            const id = submit.val();
            if (id === 'false') {
                return;
            }
            const data = {
                'id': id,
            };

            //默认成功回调钩子
            //console.log(self.hooks.CardsGood?.defultStatus);
            if (self.hooks.CardsGood?.defultStatus == undefined || self.hooks.CardsGood?.defultStatus == true) {
                //自定义回调函数
                self.SetPostCardsGoodHooks({
                    then: (response) => {
                        submit.css('display', 'none');
                        submit.after(newHtml(response.data[0]));
                    }
                }, true);
            }
            //发起请求
            self.PostCardsGood(data);
        });
    }

    //退出账户
    // BindLogout = (submitId) => {
    //     //绑定方法
    //     $('#' + submitId).click(function () { this.PostLogout() }.bind(this));
    // }

    // SetPostLogoutHooks = (intiHook, thenHook, catchJHook) => {
    //     //设置方法
    //     this.hooks.PostLogout = {};
    //     this.hooks.PostLogout.inti = intiHook;
    //     this.hooks.PostLogout.then = thenHook;
    //     this.hooks.PostLogout.catch = catchJHook;
    // }

    /**
     * 设置PostCardsGood钩子
     * @param {Object:{inti:Function,then:Function,catch:Function}} hooks 
     * @param {Boolean} defultStatus 默认为False
     * @param {String} thisHooksKey 
     */
    SetPostCardsGoodHooks = (hooks, defultStatus = false, thisHooksKey = 'CardsGood') => {
        this.SetRequestApiUrl(hooks, defultStatus, thisHooksKey);
    }

    //登出请求
    // PostLogout = () => {
    //     if (this.hooks.PostLogout?.inti) {
    //         //自定义回调函数
    //         this.hooks.PostLogout.inti();
    //     } else {
    //         this.commonFunctions.snackbar('正在发起请求！');
    //     }

    //     var data = {};

    //     //提交数据
    //     return this.Axios('post', this.config.apiUrl.AuthLogout, data).then((response) => {
    //         // this.hooks.PostLogout?.then写法解释
    //         // this.hooks.PostLogout 存在并且包含属性 then
    //         // 这将在 this.hooks.PostLogout 存在并且 then 存在的情况下执行操作
    //         if (this.hooks.PostLogout?.then) {
    //             //自定义回调函数
    //             this.hooks.PostLogout.then();
    //         } else {
    //             //默认回调函数
    //             this.DeleteAdminToken();//清除Token
    //             this.commonFunctions.snackbar('退出成功，正在跳转');
    //         }
    //     }).catch((error) => {
    //         if (this.hooks.PostLogout?.catch) {
    //             this.hooks.PostLogout.catch();
    //         } else {
    //             this.AxiosErrorHandling(error);
    //         }
    //     });
    // }
    /**
     * 点赞请求
     * @param {Object:{id:Number}} resData 
     */
    PostCardsGood = (resData) => {
        var data = {
            'id': resData.id,
        };
        this.RequestApiUrl('post', 'CardsGood', 'CardsGood', data);
    }

}