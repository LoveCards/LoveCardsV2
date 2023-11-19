class LoveCards {
    constructor() {
        this.config = {};
        this.commonFunctions = {};

        //基础
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

            TagsAdd: '/api/CardsTag/add',//添加标签
            TagsEdit: '/api/CardsTag/edit',//编辑标签
            TagsDelete: '/api/CardsTag/delete',//删除标签

            CommentsEdit: '/api/CardsComments/edit',//编辑评论
            CommentsDelete: '/api/CardsComments/delete',//删除评论

            //install
            SystemInstallVerifyEnvironment: '/system/Install/apiVerifyEnvironment',//验证环境
            SystemInstallSetDbConfig: '/system/Install/apiSetDbConfig',//配置数据库
            SystemInstallSetInstallLock: '/system/Install/apiSetInstallLock',//生成安装记录
        };

        const geetest4 = {
            CaptchaId: '145e5424cb89698be0c58a1060483735',
            CaptchaStatus: 0
        };

        const token = {
            AdminTokenName: 'TOKEN'
        };

        this.config.apiUrl = apiUrl;
        this.config.geetest4 = geetest4;
        this.config.token = token;

        this.commonFunctions.snackbar = (message) => mdui.snackbar({ message: message, position: 'right-top' });
    }

    SetCookie = (key, value, expires = 7, path = '/') => {
        $.cookie(key, value, { path: path, expires });
    }

    SetAdminToken = (token) => {
        if (token) {
            this.SetCookie(this.config.token.AdminTokenName, token)
        }
    }

    DeleteAdminToken = () => {
        if ($.removeCookie(this.config.token.AdminTokenName, { path: '/' })) {
            return true;
        }
        return false;
    }

    GetAdminToken = () => {
        if ($.cookie(this.config.token.AdminTokenName)) {
            return `Bearer ${$.cookie(this.config.token.AdminTokenName)}`;
        }
        return false;
    }

    //错误处理
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

    //通用极验校验接口
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

    //通用请求接口
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
    };
}