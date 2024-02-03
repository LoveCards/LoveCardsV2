class Index extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    //外部

    //自定义钩子

    /**
    * 设置PostDelete方法初始、请求成功、请求失败的钩子
    * @param {(Function|undefined)} intiHook 
    * @param {(Function(response)|undefined)} thanHook 
    * @param {(Function(error)|undefined)} catchJHook 
    */
    SetPostDeleteHooks = (intiHook = undefined, thanHook = undefined, catchHook = undefined) => {
        //设置方法
        this.hooks.PostDelete = {};
        this.hooks.PostDelete.inti = intiHook;
        this.hooks.PostDelete.then = thanHook;
        this.hooks.PostDelete.catch = catchHook;
    }


    //内部

    /**
     * 删除卡片
     * @param {*} id 
     */
    PostDelete = (id) => {
        this.SetPostDeleteHooks(undefined, () => {
            this.commonFunctions.snackbar('删除成功');
            this.JumpUrl('');
        });
        let data = {
            'id': id
        };

        this.RequestApiUrl('post', 'CardsDelete', 'PostDelete', data);
    }
}