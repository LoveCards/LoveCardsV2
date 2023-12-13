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

    /**
     * 登入绑定
     * @param {String} submitId 
     * @param {String} accountId 
     * @param {String} passwordId 
     */
    BindLogin = (submitId, accountId, passwordId) => {
        //默认回调函数设置
        if (this.hooks.PostLogin?.defultStatus == undefined || this.hooks.PostLogin?.defultStatus == true) {
            this.SetHooksPostLogin({
                then: (response) => {
                    //默认回调函数
                    this.SetToken(response.data.token, 'UserTokenName');//设置Token
                    this.commonFunctions.snackbar('登入成功，正在跳转');
                }
            }, true);
        }

        //绑定方法
        $('#' + submitId).click(function () {
            const data = {
                'account': $('#' + accountId).val(),
                'password': $('#' + passwordId).val()
            }
            this.PostLogin(data);
        }.bind(this));
    }

    /**
     * 退出绑定
     */
    BindLogout = (submitId) => {
        //默认回调函数设置
        if (this.hooks.PostLogout?.defultStatus == undefined || this.hooks.PostLogout?.defultStatus == true) {
            this.SetHooksPostLogout({
                then: (response) => {
                    this.DeleteToken('UserTokenName');//清除Token
                    this.commonFunctions.snackbar('退出成功，正在跳转');
                }
            }, true);
        }
        //绑定方法
        $('#' + submitId).click(function () { this.PostLogout() }.bind(this));
    }

    /**
     * 设置PostLoginHooks钩子
     * @param {RequestHooks} hooks 
     * @param {Boolean} defultStatus 默认为False
     * @param {String} thisHooksKey 
     */
    SetHooksPostLogin = (hooks, defultStatus = false, thisHooksKey = 'PostLogin') => {
        this.SetRequestApiUrlHooks(hooks, defultStatus, thisHooksKey);
    }

    /**
     * 设置PostLogoutHooks钩子
     * @param {RequestHooks} hooks 
     * @param {Boolean} defultStatus 默认为False
     * @param {String} thisHooksKey 
     */
    SetHooksPostLogout = (hooks, defultStatus = false, thisHooksKey = 'PostLogout') => {
        this.SetRequestApiUrlHooks(hooks, defultStatus, thisHooksKey);
    }

    /**
     * 设置PostCardsGood钩子
     * @param {RequestHooks} hooks 
     * @param {Boolean} defultStatus 默认为False
     * @param {String} thisHooksKey 
     */
    SetHooksPostCardsGood = (hooks, defultStatus = false, thisHooksKey = 'CardsGood') => {
        this.SetRequestApiUrlHooks(hooks, defultStatus, thisHooksKey);
    }

    /**
     * 注销请求
     */
    PostLogout = () => {
        var data = {};

        //提交数据
        this.RequestApiUrl('post', 'UserAuthLogout', 'PostLogout', data, 'UserTokenName');
    }

    /**
     * 登入请求
     * @param {*} resData 
     * @returns 
     */
    PostLogin = (resData) => {
        var data = {
            'account': resData.account,
            'password': resData.password
        };
        this.RequestApiUrl('post', 'UserAuthLogin', 'PostLogin', data, 'UserTokenName');
    }

    /**
     * 点赞请求
     * @param {Object} resData {id:Number}
     */
    PostCardsGood = (resData) => {
        var data = {
            'id': resData.id,
        };
        this.RequestApiUrl('post', 'CardsGood', 'CardsGood', data, 'UserTokenName');
    }

}