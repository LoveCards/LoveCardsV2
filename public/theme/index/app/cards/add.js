class Add extends Base {
    constructor() {
        super();
        this.hooks = {};
    }

    /**
     * 绑定提交按钮
     * @param {String} submitId 
     * @param {Function} getValue 
     */
    BindPostCardsAdd = (submitId = undefined, getValue) => {
        //绑定方法
        if (this.config.geetest4.CaptchaStatus) {
            this.Geetest4(submitId, (CaptchaData) => {
                const value = getValue();
                this.PostCardsAdd(value, CaptchaData)
            });
        } else {
            $('#' + submitId).click(() => {
                const value = getValue();
                this.PostCardsAdd(value)
            });
        }
    }

    /**
     * 设置钩子
     * @param {Function} intiHook 
     * @param {Function} thanHook 
     * @param {Function} catchJHook 
     */
    SetPostCardsAddHooks = (intiHook, thanHook, catchJHook) => {
        //设置方法
        this.hooks.PostCardsAdd = {};
        this.hooks.PostCardsAdd.inti = intiHook;
        this.hooks.PostCardsAdd.then = thanHook;
        this.hooks.PostCardsAdd.catch = catchJHook;
    }

    /**
     * 提交卡片
     * @param {Object} resData 
     * @param {Object} CaptchaData 
     */
    PostCardsAdd = (resData, CaptchaData) => {

        //是否存在验证参数
        var data;
        if (CaptchaData) { data = CaptchaData; }
        console.log(resData);
        let pictures = [];
        resData.img.forEach(element => {
            pictures.push(element.id);
        });
        let dataJson = JSON.stringify({
            'woName': resData.woName,
            'taName': resData.taName,
            'woContact': resData.woContact,
            'taContact': resData.taContact,
            'model': resData.model,
        });

        var data = {
            ...data,//合并验证参数
            'content': resData.content,
        };
        data['data'] = dataJson;
        if (resData.tag != []) {
            data['tags'] = JSON.stringify(resData.tag);
        }
        if (pictures) {
            data['pictures'] = JSON.stringify(pictures);
        }
        if (resData.img[0]) {
            data['cover'] = resData.img[0].url;
        }

        this.RequestApiUrl('post', 'Card', 'PostCardsAdd', data, 'UserTokenName');
    }
}