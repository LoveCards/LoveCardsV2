class Index extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    //外部方法

    /**
     * 绑定添加按钮和输入框
     * @param {String} submitId 
     * @param {String} nameId 
     * @param {String} tipId 
     */
    BindPostAdd = (submitId, nameId, tipId) => {
        $('#' + submitId).click(() => {
            const aid = this.config.appId.Cards;
            const name = $('#' + nameId).val();
            const tip = $('#' + tipId).val();
            this.PostAdd(aid, name, tip);
        })
    }
    /**
     * 绑定修改按钮和输入框
     * @param {String} submitId 
     * @param {String} idId 
     * @param {String} nameId 
     * @param {String} tipId 
     * @param {String} statusId 
     */
    BindPostEdit = (submitId, idId, nameId, tipId, statusId) => {
        $('#' + submitId).click(() => {
            const id = $('#' + idId).val();
            const name = $('#' + nameId).val();
            const tip = $('#' + tipId).val();
            const status = $('#' + statusId).val();
            this.PostEdit(id, name, tip, status);
        })
    }

    //自定义钩子

    /**
     * 设置PostAdd方法初始、请求成功、请求失败的钩子
     * @param {(Function|undefined)} intiHook 
     * @param {(Function(response)|undefined)} thanHook 
     * @param {(Function(error)|undefined)} catchJHook 
     */
    SetPostAddHooks = (intiHook = undefined, thanHook = undefined, catchHook = undefined) => {
        //设置方法
        this.hooks.PostAdd = {};
        this.hooks.PostAdd.inti = intiHook;
        this.hooks.PostAdd.then = thanHook;
        this.hooks.PostAdd.catch = catchHook;
    }
    /**
    * 设置PostEdit方法初始、请求成功、请求失败的钩子
    * @param {(Function|undefined)} intiHook 
    * @param {(Function(response)|undefined)} thanHook 
    * @param {(Function(error)|undefined)} catchJHook 
    */
    SetPostEditHooks = (intiHook = undefined, thanHook = undefined, catchHook = undefined) => {
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
    SetPostDeleteHooks = (intiHook = undefined, thanHook = undefined, catchHook = undefined) => {
        //设置方法
        this.hooks.PostDelete = {};
        this.hooks.PostDelete.inti = intiHook;
        this.hooks.PostDelete.then = thanHook;
        this.hooks.PostDelete.catch = catchHook;
    }

    //内部方法

    /**
     * 添加Tag
     * @param {Number} aid 
     * @param {String} name 
     * @param {String} tip 
     */
    PostAdd = (aid, name, tip) => {
        this.SetPostAddHooks(undefined, () => {
            this.commonFunctions.snackbar('添加成功');
            this.JumpUrl('');
        });
        let data = {
            'aid': aid,
            'name': name,
            'tip': tip
        };
        this.RequestApiUrl('post', 'TagsAdd', 'PostAdd', data);
    }

    /**
     * 编辑Tag
     * @param {Number} id 
     * @param {String} name 
     * @param {String} tip 
     * @param {Number} status 
     */
    PostEdit = (id, name, tip, status) => {
        this.SetPostEditHooks(undefined, () => {
            this.commonFunctions.snackbar('编辑成功');
            this.JumpUrl('');
        });
        let data = {
            'id': id,
            'aid': '#',//设为#代表不更新
            'name': name,
            'tip': tip,
            'status': status
        };

        this.RequestApiUrl('post', 'TagsEdit', 'PostEdit', data);
    }

    /**
     * 删除Tag
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

        this.RequestApiUrl('post', 'TagsDelete', 'PostDelete', data);
    }
}