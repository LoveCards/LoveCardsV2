class Index extends Base {

    constructor() {
        super();
        this.hooks = {};
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
     * 添加Admin
     * @param {Object} addData 
     */
    PostAdd = (addData) => {
        this.SetPostAddHooks(undefined, () => {
            this.commonFunctions.snackbar('添加成功');
            this.JumpUrl('');
        });
        let data = {
            'userName': addData.userName,
            'password': addData.password,
            'power': addData.power
        };
        this.RequestApiUrl('post', 'AdminAdd', 'PostAdd', data);
    }

    /**
     * 编辑Admin
     * @param {Object} editData 
     */
    PostEdit = (editData) => {
        this.SetPostEditHooks(undefined, () => {
            this.commonFunctions.snackbar('编辑成功');
            this.JumpUrl('');
        });
        let data = {
            'id': editData.id,
            'userName': editData.userName,
            'password': editData.password,
            'power': editData.power
        };

        this.RequestApiUrl('post', 'AdminEdit', 'PostEdit', data);
    }

    /**
     * 删除Admin
     * @param {Number} id 
     */
    PostDelete = (id) => {
        this.SetPostDeleteHooks(undefined, () => {
            this.commonFunctions.snackbar('删除成功');
            this.JumpUrl('');
        });
        let data = {
            'id': id
        };

        this.RequestApiUrl('post', 'AdminDelete', 'PostDelete', data);
    }
}