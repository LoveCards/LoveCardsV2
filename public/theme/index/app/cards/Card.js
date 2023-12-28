class Card extends Base {
    constructor() {
        super();
        this.hooks = {};
    }

    /**
     * 绑定评论提交按钮
     * @param {String} submitId 
     * @param {Number} pid 
     * @param {String} commentsNameId 
     * @param {String} commentsContentId 
     * @param {Number} aid 
     */
    BindPostCommentsAdd = (submitId = undefined, pid, commentsNameId, commentsContentId, aid = 1) => {

        const getValue = () => {
            const name = $('#' + commentsNameId).val();
            const content = $('#' + commentsContentId).val();
            return {
                aid: aid,
                pid: pid,
                name: name,
                content: content,
            }
        };
        //默认回调函数设置
        if (this.hooks.CommentsAdd?.defultStatus == undefined || this.hooks.CommentsAdd?.defultStatus == true) {
            this.SetPostCommentsAddHooks({
                inti: () => { },
                then: (response) => {
                    window.location.reload();
                    //console.log('提交成功刷新页面');
                }
            }, true);
        }
        //判断是否为验证模式
        if (this.config.geetest4.CaptchaStatus) {
            this.Geetest4(submitId, (CaptchaData) => {
                const value = getValue();
                this.PostCommentsAdd(value, CaptchaData)
            });
        } else {
            $('#' + submitId).click(() => {
                const value = getValue();
                this.PostCommentsAdd(value)
            });
        }
    }

    /**
     * 设置PostCommentsAdd钩子
     * @param {Object:{inti:Function,then:Function,catch:Function}} hooks 
     * @param {Boolean} defultStatus 默认为False
     * @param {String} thisHooksKey 
     */
    SetPostCommentsAddHooks = (hooks, defultStatus = false, thisHooksKey = 'PostCommentsAdd') => {
        this.SetRequestApiUrlHooks(hooks, defultStatus, thisHooksKey);
    }

    /**
     * 提交卡片
     * @param {Object} resData 
     * @param {Object} CaptchaData 
     */
    PostCommentsAdd = (resData, CaptchaData) => {

        //是否存在验证参数
        var data;
        if (CaptchaData) { data = CaptchaData; }

        var data = {
            ...data,//合并验证参数
            'aid': resData.aid,
            'pid': resData.pid,
            'name': resData.name,
            'content': resData.content,
        };
        this.RequestApiUrl('post', 'CommentsAdd', 'PostCommentsAdd', data, 'UserTokenName');
    }

}