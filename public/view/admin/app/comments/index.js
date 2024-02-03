class Index extends Base {

    constructor() {
        super();
        this.hooks = {};
        this.chart = {};
    }

    //外部

    /**
     * 绑定编辑按钮
     * @param {String} submitId 
     * @param {String} idId 
     * @param {String} nameId 
     * @param {String} contentId 
     * @param {String} statusId 
     */
    BindPostEdit = (submitId, idId, nameId, contentId, statusId) => {
        $('#' + submitId).click(() => {
            const id = $('#' + idId).val();
            const name = $('#' + nameId).val();
            const content = $('#' + contentId).val();
            const status = $('#' + statusId).val();
            this.PostEdit(id, name, content, status);
        })
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
    /**
     * 设置PostDelete方法初始、请求成功、请求失败的钩子
     * @param {(Function|undefined)} intiHook 
     * @param {(Function(response)|undefined)} thanHook 
     * @param {(Function(error)|undefined)} catchJHook 
     */
    SetPostDeleteHooks = (intiHook, thanHook, catchHook) => {
        //设置方法
        this.hooks.PostDelete = {};
        this.hooks.PostDelete.inti = intiHook;
        this.hooks.PostDelete.then = thanHook;
        this.hooks.PostDelete.catch = catchHook;
    }
    //内部

    /**
     * 编辑评论
     * @param {Number} id 
     * @param {String} name 
     * @param {String} content 
     * @param {Number} status 
     */
    PostEdit = (id, name, content, status) => {
        this.SetPostEditHooks(undefined, () => {
            this.commonFunctions.snackbar('编辑成功');
            this.JumpUrl('');
        });
        var data = {
            'id': id,
            'name': name,
            'content': content,
            'status': status
        };
        this.RequestApiUrl('post', 'CoomentsEdit', 'PostEdit', data);
    }

    /**
     * 删除评论
     * @param {*} id 
     */
    PostDelete = (id) => {
        this.SetPostEditHooks(undefined, () => {
            this.commonFunctions.snackbar('删除成功');
            this.JumpUrl('');
        });
        var data = {
            'id': id
        };
        this.RequestApiUrl('post', 'CoomentsDelete', 'PostDelete', data);
    }
}