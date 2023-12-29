class Index extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    //自定义钩子

    /**
     * 设置PostSystemSite方法初始、请求成功、请求失败的钩子
     * @param {(Function|undefined)} intiHook 
     * @param {(Function(response)|undefined)} thanHook 
     * @param {(Function(error)|undefined)} catchJHook 
     */
    SetPostSystemSiteHooks = (intiHook, thanHook, catchHook) => {
        //设置方法
        this.hooks.PostSystemSite = {};
        this.hooks.PostSystemSite.inti = intiHook;
        this.hooks.PostSystemSite.then = thanHook;
        this.hooks.PostSystemSite.catch = catchHook;
    }
    /**
     * 设置PostSystemGeetest方法初始、请求成功、请求失败的钩子
     * @param {(Function|undefined)} intiHook 
     * @param {(Function(response)|undefined)} thanHook 
     * @param {(Function(error)|undefined)} catchJHook 
     */
    SetPostSystemGeetestHooks = (intiHook, thanHook, catchHook) => {
        //设置方法
        this.hooks.PostSystemGeetest = {};
        this.hooks.PostSystemGeetest.inti = intiHook;
        this.hooks.PostSystemGeetest.then = thanHook;
        this.hooks.PostSystemGeetest.catch = catchHook;
    }

    //内部

    /**
     * 设置站点
     * @param {Object} editData 
     */
    PostSystemSite = (editData) => {
        this.SetPostSystemSiteHooks(undefined, () => {
            this.commonFunctions.snackbar('编辑成功');
        });
        const data = {
            'siteUrl': editData.siteUrl,
            'siteName': editData.siteName,
            'siteTitle': editData.siteTitle,
            'siteKeywords': editData.siteKeywords,
            'siteDes': editData.siteDes,
            'siteICPId': editData.siteICPId,
            'password': editData.Password,
            'siteCopyright': editData.siteCopyright,
        };
        this.RequestApiUrl('post', 'SystemSite', 'PostSystemSite', data);
    }

    /**
     * 设置极验
     * @param {Object} editData 
     */
    PostSystemGeetest = (editData) => {
        this.SetPostSystemGeetestHooks(undefined, () => {
            this.commonFunctions.snackbar('编辑成功');
        });
        const data = {
            'DefSetGeetestId': editData.DefSetGeetestId,
            'DefSetGeetestKey': editData.DefSetGeetestKey,
            'DefSetValidatesStatus': editData.DefSetValidatesStatus,
        };
        this.RequestApiUrl('post', 'SystemGeetest', 'PostSystemGeetest', data);
    }

}