class View extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    /**
     * 设置SystemTemplate方法初始、请求成功、请求失败的钩子
     * @param {(Function|undefined)} intiHook 
     * @param {(Function(response)|undefined)} thanHook 
     * @param {(Function(error)|undefined)} catchJHook 
     */
    SetSystemTemplateHooks = (intiHook, thanHook, catchHook) => {
        //设置方法
        this.hooks.SystemTemplate = {};
        this.hooks.SystemTemplate.inti = intiHook;
        this.hooks.SystemTemplate.then = thanHook;
        this.hooks.SystemTemplate.catch = catchHook;
    }

    //内部

    /**
     * 设置主题
     * @param {Object} editData 
     */
    PostSystemTemplate = (editData) => {
        this.SetSystemTemplateHooks(undefined, () => {
            this.commonFunctions.snackbar('设置成功');
            window.location.reload();
        });
        const data = {
            'themeDirectory': editData.themeDirectory,
        };
        this.RequestApiUrl('post', 'SystemTemplate', 'SystemTemplate', data);
    }

}