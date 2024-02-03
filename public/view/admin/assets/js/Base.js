/**
 * @typedef {Object} TokenConfig
 * @property {string} AdminTokenName - Admin token name
 * @property {string} UserTokenName - User token name
 *
 * @typedef {Object} RequestHooks
 * @property {Function} inti - 初始化
 * @property {Function} then - 成功回调
 * @property {Function} catch - 失败回调
 */
class Base {

    constructor() {
        this.config = {};
        this.commonFunctions = {};
        this.hooks = {};

        //接口
        const apiUrl = {
            AuthLogin: '/api/auth/login',//登入
            AuthLogout: '/api/auth/logout',//注销

            AdminDelete: '/api/admin/delete',//删除管理员
            AdminAdd: '/api/admin/add',//添加管理员
            AdminEdit: '/api/admin/edit',//添加管理员

            UserAuthLogin: '/api/user/auth/login',//登入
            UserAuthLogout: '/api/user/auth/logout',//注销
            UserAuthRegister: '/api/user/auth/register',//注册
            UserAuthMsgCaptcha: '/api/user/auth/captcha',//验证码
            UserAuthCheck: '/api/user/auth/check',//TOKEN校验

            UsersIndex: '/api/users/index',//列出用户
            UsersDelete: '/api/users/delete',//删除用户
            UsersAdd: '/api/users/add',//添加用户
            UsersPatch: '/api/users/patch',//添加用户

            UserInfo: '/api/user',//用户-RF
            UserPassword: '/api/user/password',//设置密码
            UserEmail: '/api/user/email',//修改邮箱
            UserEmailCaptcha: '/api/user/email-captcha',//获取修改邮箱验证码
            UploadUserImages: '/api/upload/user-images',//用户图片上传

            SystemSite: '/api/system/site',//基本信息设置
            SystemEmail: '/api/system/email',//系统邮箱设置
            SystemOther: '/api/system/other',//系统其他设置
            SystemTemplate: '/api/system/template',//主题设置
            SystemTemplateSet: '/api/system/TemplateSet',//主题配置
            SystemGeetest: '/api/system/geetest',//验证码设置

            CardsAdd: '/api/cards/add',//添加卡片
            CardsEdit: '/api/cards/edit',//编辑卡片
            CardsDelete: '/api/cards/delete',//删除卡片
            CardsSetting: '/api/cards/setting',//模块设置
            CardsGood: '/api/cards/good',//点赞

            TagsAdd: '/api/tags/add',//添加标签
            TagsEdit: '/api/tags/edit',//编辑标签
            TagsDelete: '/api/tags/delete',//删除标签

            CommentsAdd: '/api/comments/add',//编辑评论
            CommentsEdit: '/api/comments/edit',//编辑评论
            CommentsDelete: '/api/comments/delete',//删除评论

            ThemeConfig: '/api/theme/config',//主题配置

            //install
            SystemInstallCheckEnvironment: '/system/Install/GetCheckEnvironment',//验证环境
            SystemInstallCreateRsa: '/system/Install/PostCreateRsa',//创建RSA密钥
            SystemInstallSetDbConfig: '/system/Install/PostDbConfig',//配置数据库
            SystemInstallSetInstallLock: '/system/Install/PostInstallLock',//生成安装记录
            SystemInstallGetVersionInfo: '/system/Install/GetVersionInfo',//
        };

        //极验配置
        const geetest4 = {
            CaptchaId: '',
            CaptchaStatus: 0
        };

        //Token配置
        const token = {
            AdminTokenName: 'TOKEN',
            UserTokenName: 'UTOKEN'
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
        //this.hooks.undefined = {};

        this.commonFunctions.snackbar = (message) => mdui.snackbar({ message: message, position: 'right-top' });
    }

    /**
     * 加载主题配置
     * @returns {Promise}
     */
    ThemeInit = () => {
        //获取极验配置
        return this.RequestApiUrl('get', 'ThemeConfig', undefined, [], 'UserTokenName').then((req) => {
            this.config.geetest4.CaptchaId = req.data.system.config.file.class.geetest.DefSetGeetestId;
            this.config.geetest4.CaptchaStatus = Number(req.data.system.config.file.class.geetest.DefSetValidatesStatus);
            //console.log(this.config.geetest4);
            return req;
        }).catch((err) => {
            this.commonFunctions.snackbar('获取配置信息失败,请刷新页面后再试！');
            throw err;
        });
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
    // BindJumpUrl = (elementId, url, time) => {
    //     $('#' + elementId).click(() => {
    //         this.JumpUrl(url, time)
    //     });
    // }

    /**
     * 读取Cookie
     * @param {*} key 
     * @returns 
     */
    GetCookie = (key) => {
        const cookies = document.cookie.split(';').map(cookie => cookie.trim().split('='));
        const cookie = cookies.find(([cookieKey]) => cookieKey === key);
        return cookie ? cookie[1] : null;
    }
    /**
     * Cookie设置
     * @param {String} key 
     * @param {String} value 
     * @param {Number} expires //def=7
     * @param {String} path //def='/'
     */
    SetCookie = (key, value, expires = 7, path = '/') => {
        const expirationDate = new Date();
        expirationDate.setDate(expirationDate.getDate() + expires);

        const cookieString = `${key}=${value}; expires=${expirationDate.toUTCString()}; path=${path}`;
        document.cookie = cookieString;
    };
    /**
     * Cookie删除
     * @param {*} key 
     * @param {*} path 
     */
    DeleteCookie = (key, path = '/') => {
        const expirationDate = new Date();
        expirationDate.setFullYear(expirationDate.getFullYear() - 1);

        const cookieString = `${key}=; expires=${expirationDate.toUTCString()}; path=${path}`;
        document.cookie = cookieString;
    };

    /**
     * 设置Token Cookie
     * @param {String} token 
     * @param {TokenConfig} tokenName 
     */
    SetToken = (token, tokenName) => {
        if (token) {
            this.SetCookie(this.config.token[tokenName], token)
        }
    }
    /**
     * 删除Token Cookie
     * @param {TokenConfig} tokenName 
     * @returns 
     */
    DeleteToken = (tokenName) => {
        if (this.DeleteCookie(this.config.token[tokenName], { path: '/' })) {
            return true;
        }
        return false;
    }
    /**
     * 获取Token Cookie
     * @param {TokenConfig} tokenName 
     * @returns 
     */
    GetToken = (tokenName) => {
        if (this.GetCookie(this.config.token[tokenName])) {
            return `Bearer ${this.GetCookie(this.config.token[tokenName])}`;
        }
        return false;
    }

    /**
     * Axios通用error snackbar处理
     * @param {Object} error 
     */
    AxiosErrorHandling = (error) => {
        if (!(error.response?.data)) {
            console.log('非请求错误，请检查代码！')
            console.log(error);
            return;
        }
        const responseError = error.response.data.error;
        let responseDetail = error.response.data.detail;
        let result = responseError;
        //如果存在提示也渲染出来
        if (responseDetail?.length !== 0) {
            result = responseError + ':';
            //转键值数组报错为无序数组
            if (Object.keys(responseDetail).length > 0 && typeof (responseDetail) != 'string') {
                responseDetail = Object.values(responseDetail);
                responseDetail.forEach(element => {
                    result += ' ' + element;
                });
            } else {
                result += ' ' + responseDetail;
            }

        }
        this.commonFunctions.snackbar(result);
    }

    /**
     * 通用极验校验接口
     * @param {String} submitId 
     * @param {Function} PostFunction 
     */
    Geetest4 = (submitId, PostFunction) => {
        const button = document.getElementById(submitId);
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
            button.addEventListener('click', function () {
                captcha.showBox(); //显示验证码
            });
        });
    }

    /**
    * AXios封装加入拦截器
    * @param {String} method //axios的method
    * @param {String} url 
    * @param {Object} data 
    * @param {Object} headers
    * @returns {Promise}
    */
    Axios = (method, url, data, headers = {}) => {
        // 添加请求拦截器
        axios.interceptors.request.use((config) => {
            //载入钩子
            if (this.hooks.Axios?.request) {
                //自定义回调函数
                this.hooks.Axios.request(config);
            }
            return config;
        }, (error) => {
            console.log('请求拦截器报错');
            return Promise.reject(error);
        });

        // 添加响应拦截器
        axios.interceptors.response.use((response) => {
            if (this.hooks.Axios?.response) {
                //自定义回调函数
                this.hooks.Axios.response(response);
            }
            return response;
        }, (error) => {
            console.log('响应拦截器报错');
            return Promise.reject(error);
        });

        let ReqObj = {
            method: method,
            url: url,
            headers: headers
        };

        if (method.toLowerCase() != 'get') {
            ReqObj.data = data;
        } else {
            ReqObj.params = data;
        }

        // 执行
        return axios(ReqObj);
    }

    /**
     * RequestApiUrl设置Hooks
     * @param {RequestHooks} hooks 
     * @param {Boolean} defultStatus //标记状态当.defultStatus 为True时则为默认钩子设置
     * @param {String} thisHooksKey 
     */
    SetRequestApiUrlHooks = (hooks, defultStatus, thisHooksKey) => {
        this.hooks[thisHooksKey] = {};
        if (hooks?.inti) {
            this.hooks[thisHooksKey].inti = hooks.inti;
        }
        if (hooks?.then) {
            this.hooks[thisHooksKey].then = hooks.then;
        }
        if (hooks?.catch) {
            this.hooks[thisHooksKey].catch = hooks.catch;
        }
        this.hooks[thisHooksKey].defultStatus = defultStatus;
    }

    /**
     * ApiUrl通用请求接口
     * @param {String} method //Axios的method
     * @param {String} thisConfigApiUrlKey //this.config.apiUrl中查找
     * @param {String|RequestHooks} thisHooksKey 
     * //当前子类的this.Hooks中查找 可通过当前子类提供的设置方法去更改 
     * //当传入RequestHooks时将以最高级替换其他方式传入方法
     * //当为undefined时将不再是Hooks模式而返回原始Promise 
     * //当使用RequestHooks传入时将激活内部的Promise默认方法
     * @param {Object} data //参数对象 可传入请求头 将自动分离 ReqHeaders
     * @param {TokenConfig} tokenName //携带TOKEN
     * 
     * @returns {Promise}
     */
    RequestApiUrl = (method, thisConfigApiUrlKey, thisHooksKey = undefined, data = {}, tokenName = 'AdminTokenName') => {
        //判断Url是否存在
        if (!this.config.apiUrl[thisConfigApiUrlKey]) {
            return Promise.reject('注意：' + thisConfigApiUrlKey + '不存在于this.config.apiUrl');
        }

        //设置初始化方法 AI优化逻辑
        if (thisHooksKey != undefined) {
            const isObject = typeof thisHooksKey === 'object' && thisHooksKey !== null;

            if (isObject && thisHooksKey.inti) {
                // 自定义回调函数
                thisHooksKey.inti(data);
            } else if (this.hooks[thisHooksKey]?.inti) {
                // 自定义回调函数
                this.hooks[thisHooksKey].inti(data);
            } else {
                // 默认回调函数
                this.commonFunctions.snackbar(thisConfigApiUrlKey + '发起请求');
            }
        }

        //设置Axios钩子
        this.hooks.Axios = {
            /**
             * 请求拦截器钩子
             * @param {*} config 
             * @returns 
             */
            request: (config) => {
                //插入token
                const token = this.GetToken(tokenName);
                if (token) {
                    config.headers["Authorization"] = token;
                }
                return config;
            },
            /**
             * 响应拦截器钩子
             * @param {*} response 
             * @returns 
             */
            response: (response) => {
                //刷新Token
                const token = response.headers["token"];
                if (token) {
                    this.SetToken(tokenName);
                }
                return response;
            }
        }

        //设置请求头
        let Headers = {};
        if (data?.ReqHeaders) {
            Headers = data.ReqHeaders;
            delete data.ReqHeaders;
        }

        //返回请Axios请求方法 AI优化逻辑
        if (thisHooksKey != undefined) {
            const axiosPromise = this.Axios(method, this.config.apiUrl[thisConfigApiUrlKey], data, Headers);

            axiosPromise.then((response) => {
                const thenCallback = thisHooksKey?.then || this.hooks[thisHooksKey]?.then;
                if (thenCallback) {
                    thenCallback(response);
                } else {
                    this.commonFunctions.snackbar(thisConfigApiUrlKey + '请求成功');
                    // this.JumpUrl('');
                }
            }).catch((error) => {
                const catchCallback = thisHooksKey?.catch || this.hooks[thisHooksKey]?.catch;
                if (catchCallback) {
                    catchCallback(error);
                } else {
                    this.AxiosErrorHandling(error);
                }
            });

            return axiosPromise;
        }


        //返回原始请求方法
        return this.Axios(method, this.config.apiUrl[thisConfigApiUrlKey], data, Headers);
    }

}