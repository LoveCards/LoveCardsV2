class Index extends Base {

    constructor() {
        super();
        this.hooks = {};
    }

    //自定义钩子

    //内部

    /**
     * 设置站点
     * @param {Object} editData 
     */
    PostSystemSite = (editData) => {
        const data = {
            'siteUrl': editData.siteUrl,
            'siteName': editData.siteName,
            'siteTitle': editData.siteTitle,
            'siteKeywords': editData.siteKeywords,
            'siteDes': editData.siteDes,
            'siteICPId': editData.siteICPId,
            'password': editData.Password,
            'siteCopyright': editData.siteCopyright,
        };
        this.RequestApiUrl('post', 'SystemSite', {
            inti: () => { },
            then: () => {
                this.commonFunctions.snackbar('编辑成功')
            }
        }, data);
    }

    /**
     * 设置极验
     * @param {Object} editData 
     */
    PostSystemGeetest = (editData) => {
        const data = {
            'DefSetGeetestId': editData.DefSetGeetestId,
            'DefSetGeetestKey': editData.DefSetGeetestKey,
            'DefSetValidatesStatus': editData.DefSetValidatesStatus,
        };
        this.RequestApiUrl('post', 'SystemGeetest', {
            inti: () => { },
            then: () => {
                this.commonFunctions.snackbar('编辑成功')
            }
        }, data);
    }

    /**
     * 设置邮箱
     * @param {Object} editData 
     */
    PostSystemEmail = (editData) => {
        const data = {
            'driver': editData.driver,
            'security': editData.security,
            'host': editData.host,
            'port': editData.port,
            'addr': editData.addr,
            'pass': editData.pass,
            'name': editData.name,
        };
        this.RequestApiUrl('post', 'SystemEmail', {
            inti: () => { },
            then: () => {
                this.commonFunctions.snackbar('编辑成功')
            }
        }, data);
    }

    /**
     * 显示配置
     * @returns 
     */
    GetSystemEmail = () => {
        //this.SetPostSystemEmailHooks(undefined);
        return this.RequestApiUrl('get', 'SystemEmail');
    }

    /**
     * 设置邮箱
     * @param {Object} editData 
     */
    PostSystemOther = (editData) => {
        const data = editData;
        // const data = {
        //     'VisitorMode': editData.VisitorMode,
        //     'UserImageSize': editData.UserImageSize,
        //     'UserImageExt': editData.UserImageExt,
        // };
        this.RequestApiUrl('post', 'SystemOther', {
            inti: () => { },
            then: () => {
                this.commonFunctions.snackbar('编辑成功')
            }
        }, data);
    }

    /**
     * 
     * @returns 
     */
    GetSystemOther = () => {
        //this.SetPostSystemOtherHooks(undefined);
        return this.RequestApiUrl('get', 'SystemOther');
    }
}