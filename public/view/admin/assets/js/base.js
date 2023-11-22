class Base {

    constructor() {
        this.config = {};
        this.commonFunctions = {};

        //接口
        const apiUrl = {
            AuthLogin: '/api/auth/login',//登入
            AuthLogout: '/api/auth/logout',//注销

            AdminDelete: '/api/admin/delete',//删除用户
            AdminAdd: '/api/admin/add',//添加用户
            AdminEdit: '/api/admin/edit',//添加用户

            SystemSite: '/api/system/site',//系统设置
            SystemEmail: '/api/system/email',//系统邮箱设置
            SystemTemplate: '/api/system/template',//主题设置
            SystemTemplateSet: '/api/system/TemplateSet',//主题配置
            SystemGeetest: '/api/system/geetest',//验证码设置

            cardsAdd: '/api/cards/add',//添加卡片
            cardsEdit: '/api/cards/edit',//编辑卡片
            cardsDelete: '/api/cards/delete',//删除卡片
            cardsSetting: '/api/cards/setting',//模块设置

            TagsAdd: '/api/tags/add',//添加标签
            TagsEdit: '/api/tags/edit',//编辑标签
            TagsDelete: '/api/tags/delete',//删除标签

            CommentsEdit: '/api/comments/edit',//编辑评论
            CommentsDelete: '/api/comments/delete',//删除评论

            //install
            SystemInstallVerifyEnvironment: '/system/Install/apiVerifyEnvironment',//验证环境
            SystemInstallSetDbConfig: '/system/Install/apiSetDbConfig',//配置数据库
            SystemInstallSetInstallLock: '/system/Install/apiSetInstallLock',//生成安装记录
        };

        //极验配置
        const geetest4 = {
            CaptchaId: '145e5424cb89698be0c58a1060483735',
            CaptchaStatus: 0
        };

        //token配置
        const token = {
            AdminTokenName: 'TOKEN'
        };

        //应用ID
        const appId = {
            Cards: 1,
            Comment: 2
        }

        this.config.apiUrl = apiUrl;
        this.config.geetest4 = geetest4;
        this.config.token = token;
        this.config.appId = appId;

        this.commonFunctions.snackbar = (message) => mdui.snackbar({ message: message, position: 'right-top' });
    }

    /**
     * 延时跳转方法
     * @param {String} url 
     * @param {Number} time 
     */
    JumpUrl = (url, time = 600) => {
        setTimeout(function () {
            window.location.replace(url);
        }, time);
    }

    /**
     * ID按钮绑定-延时跳转方法
     * @param {String} elementId 
     * @param {String} url 
     * @param {Number} time 
     */
    BindJumpUrl = (elementId, url, time) => {
        $('#' + elementId).click(() => {
            this.JumpUrl(url, time)
        });
    }

    /**
     * Cookie设置
     * @param {String} key 
     * @param {String} value 
     * @param {Number} expires //def=7
     * @param {String} path //def='/'
     */
    SetCookie = (key, value, expires = 7, path = '/') => {
        $.cookie(key, value, { path: path, expires });
    }

    /**
     * 设置Admin Token Cookie
     * @param {String} token 
     */
    SetAdminToken = (token) => {
        if (token) {
            this.SetCookie(this.config.token.AdminTokenName, token)
        }
    }
    /**
     * 删除Admin Token Cookie
     */
    DeleteAdminToken = () => {
        if ($.removeCookie(this.config.token.AdminTokenName, { path: '/' })) {
            return true;
        }
        return false;
    }
    /**
     * 读取Admin Token Cookie
     */
    GetAdminToken = () => {
        if ($.cookie(this.config.token.AdminTokenName)) {
            return `Bearer ${$.cookie(this.config.token.AdminTokenName)}`;
        }
        return false;
    }

    /**
     * Axios通用error snackbar处理
     * @param {Object} error 
     */
    AxiosErrorHandling = (error) => {
        const responseError = error.response.data.error;
        var responseDetail = error.response.data.detail;
        var result = responseError;
        //如果存在提示也渲染出来
        if (responseDetail?.length !== 0) {
            result = responseError + ':';
            //转键值数组报错为无序数组
            if (Object.keys(responseDetail).length > 0) {
                responseDetail = Object.values(responseDetail);
            }
            responseDetail.forEach(element => {
                result += ' ' + element;
            });
        }
        this.commonFunctions.snackbar(result);
    }

    /**
     * 通用极验校验接口
     * @param {String} submitId 
     * @param {Function} PostFunction 
     */
    Geetest4 = (submitId, PostFunction) => {
        const button = $('#' + submitId);
        const CaptchaId = this.config.geetest4.CaptchaId;
        //验证
        initGeetest4({
            captchaId: CaptchaId,
            product: 'bind'
        }, function (captcha) {
            // captcha为验证码实例
            captcha.onReady(function () {
                //验证码ready之后才能调用verify方法显示验证码
            }).onSuccess(function () {
                var CaptchaData = captcha.getValidate();
                if (!CaptchaData) {
                    return alert('请完成验证');
                }
                CaptchaData.captcha_id = CaptchaId;
                //实例化函数并传入验证参数
                PostFunction(CaptchaData);
            }).onError(function () {
                //your code
            })
            // 按钮提交事件
            button.click(() => {
                captcha.showBox(); //显示验证码
            });
        });
    }

    /**
    * AXios封装加入拦截器
    * @param {String} method //axios的method
    * @param {String} url 
    * @param {String} data 
    * @returns {Promise}
    */
    Axios = (method, url, data) => {
        // 添加请求拦截器
        axios.interceptors.request.use((config) => {
            //插入token
            const token = this.GetAdminToken();
            if (token) {
                config.headers["Authorization"] = token;
            }

            return config;
        }, (error) => {
            console.log('请求拦截器报错');
            return Promise.reject(error);
        });

        // 添加响应拦截器
        axios.interceptors.response.use((response) => {
            //刷新Token
            const token = response.headers["token"];
            this.SetAdminToken(token);

            return response;
        }, (error) => {
            console.log('响应拦截器报错');
            return Promise.reject(error);
        });

        // 执行
        return axios({
            method: method,
            url: url,
            data: data
        });
    }

    /**
     * ApiUrl通用请求接口
     * @param {String} method //Axios的method
     * @param {String} thisConfigApiUrlKey //this.config.apiUrl中查找
     * @param {String} thisHooksKey //当前子类的this.Hooks中查找 可通过当前子类提供的设置方法去更改
     * @param {object} data //参数对象
     * @returns {Promise}
     */
    RequestApiUrl = (method, thisConfigApiUrlKey, thisHooksKey, data) => {
        if (this.hooks[thisHooksKey]?.inti) {
            //自定义回调函数
            this.hooks[thisHooksKey].inti();
        } else {
            this.commonFunctions.snackbar(thisConfigApiUrlKey + '发起请求');
        }

        //提交数据
        return this.Axios(method, this.config.apiUrl[thisConfigApiUrlKey], data).then((response) => {
            if (this.hooks[thisHooksKey]?.then) {
                //自定义回调函数
                this.hooks[thisHooksKey].then(response);
            } else {
                //默认回调函数
                this.commonFunctions.snackbar(thisConfigApiUrlKey + '请求成功');
                //this.JumpUrl('');
            }
        }).catch((error) => {
            if (this.hooks[thisHooksKey]?.catch) {
                this.hooks[thisHooksKey].catch(error);
            } else {
                this.AxiosErrorHandling(error);
            }
        });
    }

}