class Setting extends Base {

    constructor() {
        super();
        this.hooks = {};
    }


    BindPostEdit = (
        submitId,
        DefSetCardsImgNum,
        DefSetCardsTagNumId,
        DefSetCardsStatusId,
        DefSetCardsImgSizeId,
        DefSetCardsCommentsStatusId
    ) => {
        $('#' + submitId).click(() => {
            let data = {
                'DefSetCardsImgNum': $('#' + DefSetCardsImgNum).val(),
                'DefSetCardsTagNum': $('#' + DefSetCardsTagNumId).val(),
                'DefSetCardsStatus': $('#' + DefSetCardsStatusId).val(),
                'DefSetCardsImgSize': $('#' + DefSetCardsImgSizeId).val(),
                'DefSetCardsCommentsStatus': $('#' + DefSetCardsCommentsStatusId).val()
            };
            this.PostEdit(data);
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

    //内部

    /**
     * 设置卡片
     * @param {Object} editData 
     */
    PostEdit = (editData) => {
        this.SetPostEditHooks(undefined, () => {
            this.commonFunctions.snackbar('编辑成功');
        });
        var data = {
            'DefSetCardsImgNum': editData.DefSetCardsImgNum,
            'DefSetCardsTagNum': editData.DefSetCardsTagNum,
            'DefSetCardsStatus': editData.DefSetCardsStatus,
            'DefSetCardsImgSize': editData.DefSetCardsImgSize,
            'DefSetCardsCommentsStatus': editData.DefSetCardsCommentsStatus
        };
        this.RequestApiUrl('post', 'CardsSetting', 'PostEdit', data);
    }

}