class Edit extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    //自定义钩子

    /**
     * 设置PostEdit方法初始、请求成功、请求失败的钩子
     * @param {(Function|undefined)} intiHook 
     * @param {(Function(response)|undefined)} thanHook 
     * @param {(Function(error)|undefined)} catchJHook 
     */
    SetPostEditHooks = (intiHook, thanHook, catchHook) => {
        //设置方法
        this.hooks.PostEdit = {};
        this.hooks.PostEdit.inti = intiHook;
        this.hooks.PostEdit.then = thanHook;
        this.hooks.PostEdit.catch = catchHook;
    }

    //内部

    /**
     * 
     * @param {Object} editData 
     */
    PostEdit = (editData) => {
        this.SetPostEditHooks(undefined, () => {
            this.commonFunctions.snackbar('编辑成功');
        });
        var data = {
            'id': editData.id,
            'uid': editData.uid,
            'top': editData.top,
            'status': editData.status,
            'model': editData.model,
            'good': editData.good,
            'content': editData.content,
            'look': editData.look,
            'woName': editData.woName,
            'taName': editData.taName,
            'woContact': editData.woContact,
            'taContact': editData.taContact,
            'model': editData.model,
            'tag': editData.tag,
            'img': editData.img
        };
        this.RequestApiUrl('post', 'CardsEdit', 'PostEdit', data);
    }
}