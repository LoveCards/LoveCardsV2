class ViewSet extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    /**
     * 设置SystemTemplateSet方法初始、请求成功、请求失败的钩子
     * @param {(Function|undefined)} intiHook 
     * @param {(Function(response)|undefined)} thanHook 
     * @param {(Function(error)|undefined)} catchJHook 
     */
    SetSystemTemplateSetHooks = (intiHook, thanHook, catchHook) => {
        //设置方法
        this.hooks.SystemTemplateSet = {};
        this.hooks.SystemTemplateSet.inti = intiHook;
        this.hooks.SystemTemplateSet.then = thanHook;
        this.hooks.SystemTemplateSet.catch = catchHook;
    }

    //内部

    /**
     * 设置主题
     * @param {Object} editData 
     */
    PostSystemTemplateSet = (editData) => {
        this.SetSystemTemplateSetHooks(undefined, () => {
            this.commonFunctions.snackbar('编辑成功');
        });
        const data = {
            'select': editData.select,
            'text': editData.text,
        };
        this.RequestApiUrl('post', 'SystemTemplateSet', 'SystemTemplateSet', data);
    }

}