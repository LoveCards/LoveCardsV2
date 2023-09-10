//基础
var apiUrlAuthlogin = '/api/auth/login';//登入
var apiUrlAuthlogout = '/api/auth/logout';//注销

var apiAdminDelete = '/api/admin/delete';//删除用户
var apiAdminAdd = '/api/admin/add';//添加用户
var apiAdminEdit = '/api/admin/edit';//添加用户

var apiSystemSite = '/api/system/site';//系统设置
var apiSystemEmail = '/api/system/email';//系统邮箱设置
var apiSystemTemplate = '/api/system/template';//主题设置
var apiSystemTemplateSet = '/api/system/TemplateSet';//主题配置
var apiSystemGeetest = '/api/system/geetest';//验证码设置

var apicardsAdd = '/api/cards/add';//添加卡片
var apicardsEdit = '/api/cards/edit';//编辑卡片
var apicardsDelete = '/api/cards/delete';//删除卡片
var apicardsSetting = '/api/cards/setting';//模块设置

var apiCardsTagAdd = '/api/CardsTag/add';//添加标签
var apiCardsTagEdit = '/api/CardsTag/edit';//编辑标签
var apiCardsTagDelete = '/api/CardsTag/delete';//删除标签

var apiCardsCommentsEdit = '/api/CardsComments/edit';//编辑评论
var apiCardsCommentsDelete = '/api/CardsComments/delete';//删除评论

//install
var apiSystemInstallVerifyEnvironment = '/system/Install/apiVerifyEnvironment';//验证环境
var apiSystemInstallSetDbConfig = '/system/Install/apiSetDbConfig';//配置数据库
var apiSystemInstallSetInstallLock = '/system/Install/apiSetInstallLock';//生成安装记录

const GithubDateRelesesLatest = () => {
    return new Promise((resolve, reject) => {
        const storedData = $.cookie('GithubDateRelesesLatest');
        const currentTime = new Date();
        const FunSetCookie = (data) => {
            const time = new Date();
            const result = { 'time': time, 'data': data };
            $.cookie('GithubDateRelesesLatest', JSON.stringify(result), { expires: 1, path: '/' });
        }

        if (storedData) {
            var storedDataParse = JSON.parse(storedData);
        }
        if (!storedData || !storedDataParse.time || currentTime - new Date(storedDataParse.time) >= 600000) {
            // 如果没有存储数据或数据已过期（超过十分钟），则重新请求
            $.ajax({
                url: 'https://api.github.com/repos/zhiguai/LoveCards/releases/latest',
                method: 'GET',
                success: function (data) {
                    delete data.reactions;
                    delete data.body;
                    delete data.assets;
                    // 请求成功，存储数据并返回
                    FunSetCookie(data);
                    resolve(data);
                },
                error: function () {
                    // 请求失败，返回错误信息
                    FunSetCookie('error');
                    resolve('error');
                }
            });
        } else {
            // 如果数据未过期，直接返回存储的数据
            resolve(storedDataParse.data);
        }
    })
};