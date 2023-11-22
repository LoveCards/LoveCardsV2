class Index extends Base {

    constructor() {
        super();
        this.hooks = {};
        this.chart = {};
        this.data = {};
    }

    //外部
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
    SetPostEditHooks = (intiHook, thanHook, catchHook) => {
        //设置方法
        this.hooks.PostEdit = {};
        this.hooks.PostEdit.inti = intiHook;
        this.hooks.PostEdit.then = thanHook;
        this.hooks.PostEdit.catch = catchHook;
    }
    SetPostDeleteHooks = (intiHook, thanHook, catchHook) => {
        //设置方法
        this.hooks.PostDelete = {};
        this.hooks.PostDelete.inti = intiHook;
        this.hooks.PostDelete.then = thanHook;
        this.hooks.PostDelete.catch = catchHook;
    }
    //内部
    PostEdit = (id, name, content, status) => {
        if (this.hooks.PostEdit?.inti) {
            //自定义回调函数
            this.hooks.PostEdit.inti();
        } else {
            this.commonFunctions.snackbar('正在发起请求！');
        }

        var data = {
            'id': id,
            'name': name,
            'content': content,
            'status': status
        };

        //提交数据
        return this.Axios('post', this.config.apiUrl.CommentsEdit, data).then((response) => {
            if (this.hooks.PostEdit?.then) {
                //自定义回调函数
                this.hooks.PostEdit.then();
            } else {
                //默认回调函数
                this.commonFunctions.snackbar('修改成功');
                this.JumpUrl('');
            }
        }).catch((error) => {
            if (this.hooks.PostEdit?.catch) {
                this.hooks.PostEdit.catch();
            } else {
                this.AxiosErrorHandling(error);
            }
        });
    }
    PostDelete = (id) => {
        if (this.hooks.PostDelete?.inti) {
            //自定义回调函数
            this.hooks.PostDelete.inti();
        } else {
            this.commonFunctions.snackbar('正在发起请求！');
        }

        var data = {
            'id': id
        };

        //提交数据
        return this.Axios('post', this.config.apiUrl.CommentsDelete, data).then((response) => {
            if (this.hooks.PostDelete?.then) {
                //自定义回调函数
                this.hooks.PostDelete.then();
            } else {
                //默认回调函数
                this.commonFunctions.snackbar('删除成功');
                this.JumpUrl('');
            }
        }).catch((error) => {
            if (this.hooks.PostDelete?.catch) {
                this.hooks.PostDelete.catch();
            } else {
                this.AxiosErrorHandling(error);
            }
        });
    }
}