class Common extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    /**
     * Cookie传输的提示处理
     */
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
     * @param {String} submitClass 提交按钮
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
                self.SetHooksPostCardsGood({
                    inti: () => { },
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
     * @param {String} submitId 提交按钮
     * @param {String} accountId 账户输入框
     * @param {String} passwordId 密码输入框
     */
    BindLogin = (submitId, accountId, passwordId, remberMeId) => {
        //默认回调函数设置
        if (this.hooks.PostLogin?.defultStatus == undefined || this.hooks.PostLogin?.defultStatus == true) {
            this.SetHooksPostLogin({
                inti: (data) => {
                    //处理数据
                },
                then: (response) => {
                    //默认回调函数
                    this.SetToken(response.data.token, 'UserTokenName');//设置Token
                    this.commonFunctions.snackbar('登入成功，正在跳转');
                    window.location.reload();
                }
            }, true);
        }

        //绑定方法
        $('#' + submitId).click(function () {
            const data = {
                'account': $('#' + accountId).val(),
                'password': $('#' + passwordId).val()
            }
            let rememberMe = $('#' + remberMeId).prop('checked');
            if (!rememberMe) {
                this.commonFunctions.snackbar('请同意隐私政策');
                return;
            }
            if (data.account == '' || data.password == '') {
                this.commonFunctions.snackbar('账号或密码不能为空');
                return;
            }
            this.PostLogin(data);
        }.bind(this));
    }
    /**
     * 注册绑定
     * @param {String} submitId 提交按钮
     * @param {String} accountId 账户输入框
     * @param {String} passwordId 密码输入框
     * @param {String} repasswordId 重复密码输入框
     * @param {String} codeId 验证码输入框
     */
    BindRegister = (submitId, accountId, passwordId, repasswordId, codeId, remberMeId) => {
        //默认回调函数设置
        if (this.hooks.PostRegister?.defultStatus == undefined || this.hooks.PostRegister?.defultStatus == true) {
            this.SetHooksPostRegister({
                inti: () => { },
                then: (response) => {
                    //默认回调函数
                    this.SetToken(response.data.token, 'UserTokenName');//设置Token
                    this.commonFunctions.snackbar('注册成功，正在跳转');
                    window.location.reload();
                }
            }, true);
        }

        //绑定方法
        $('#' + submitId).click(function () {
            const data = {
                'account': $('#' + accountId).val(),
                'password': $('#' + passwordId).val(),
                'repassword': $('#' + repasswordId).val(),
                'code': $('#' + codeId).val()
            }
            let rememberMe = $('#' + remberMeId).prop('checked');
            if (!rememberMe) {
                this.commonFunctions.snackbar('请同意隐私政策');
                return;
            }
            if (data['repassword'] != data['password']) {
                this.commonFunctions.snackbar('两次密码不一致');
            }
            this.PostRegister(data);
        }.bind(this));
    }
    /**
     * 发送短信验证码
     * @param {*} submitId 提交按钮
     * @param {*} newHtml 间隔方法
     */
    BindSendMsgCaptcha = (submitId, accountId, newHtml, second = 60) => {
        //导入this
        const self = this;
        //绑定方法
        $('#' + submitId).click(function () {
            const submit = $(this);
            //默认回调函数设置
            if (self.hooks.PostMsgCaptcha?.defultStatus == undefined || self.hooks.PostMsgCaptcha?.defultStatus == true) {
                self.SetHooksPostMsgCaptcha({
                    inti: () => {
                        self.commonFunctions.snackbar('正在发送');
                        submit.attr('disabled', true);
                    },
                    then: (response) => {
                        self.commonFunctions.snackbar('发送成功，请注意查收');
                        submit.attr('disabled', false);
                        submit.css('display', 'none');
                        const countdown = second; // 设定倒计时时间
                        submit.after(newHtml);
                        const newBtn = submit.next();

                        let remainingTime = countdown;
                        const timer = setInterval(() => {
                            remainingTime--;
                            newBtn.text('剩余 ' + remainingTime + ' 秒');
                            if (remainingTime <= 0) {
                                clearInterval(timer);
                                newBtn.remove(); // 倒计时结束后移除提示消息
                                submit.css('display', '');
                            }
                        }, 1000); // 每秒更新一次倒计时
                    },
                    catch: (error) => {
                        submit.attr('disabled', false);
                        self.AxiosErrorHandling(error)
                    }
                }, true);
            }
            const data = {
                'account': $('#' + accountId).val(),
            }
            self.PostMsgCaptcha(data);
        });
    }
    /**
     * 退出绑定
     */
    BindLogout = (submitId) => {
        //默认回调函数设置
        if (this.hooks.PostLogout?.defultStatus == undefined || this.hooks.PostLogout?.defultStatus == true) {
            this.SetHooksPostLogout({
                inti: () => { },
                then: (response) => {
                    this.DeleteToken('UserTokenName');//清除Token
                    this.commonFunctions.snackbar('退出成功，正在跳转');
                    window.location.reload();
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
     * 设置PostRegisterHooks钩子
     * @param {RequestHooks} hooks 
     * @param {Boolean} defultStatus 默认为False
     * @param {String} thisHooksKey 
     */
    SetHooksPostRegister = (hooks, defultStatus = false, thisHooksKey = 'PostRegister') => {
        this.SetRequestApiUrlHooks(hooks, defultStatus, thisHooksKey);
    }
    /**
     * 设置PostMsgCaptchaHooks钩子
     * @param {RequestHooks} hooks 
     * @param {Boolean} defultStatus 默认为False
     * @param {String} thisHooksKey 
     */
    SetHooksPostMsgCaptcha = (hooks, defultStatus = false, thisHooksKey = 'PostMsgCaptcha') => {
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
     * 注册请求
     * @param {Object} resData 
     * @returns 
     */
    PostRegister = (resData) => {
        var data = {
            'account': resData.account,
            'password': resData.password,
            'code': resData.code
        };
        this.RequestApiUrl('post', 'UserAuthRegister', 'PostRegister', data, 'UserTokenName');
    }
    /**
     * 短信验证码请求
     * @param {Object} resData 
     * @returns 
     */
    PostMsgCaptcha = (resData) => {
        var data = {
            'account': resData.account
        };
        this.RequestApiUrl('post', 'UserAuthMsgCaptcha', 'PostMsgCaptcha', data, 'UserTokenName');
    }
    /**
     * 登入请求
     * @param {Object} resData 
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