const { createApp } = Vue;
const BaseEntity = new Base();

createApp({
    data() {
        return {
            tab: {
                activeIndex: 0,
                nowTabText: '🤗HI~LoveCards',
                btnText: '下一步',
                endBtnShow: false
            },
            //请求
            req: {
                DbConfig: {
                    hostname: "localhost",
                    database: "",
                    username: "",
                    password: "",
                    hostport: "3306",
                    pass: "",
                    force: ""
                },
                CreateRsa: {
                    public: "",
                    private: ""
                }
            },
            //步骤
            step: {
                Environment: false,
                DbConfig: false,
                CreateRsa: false
            },
            //返回
            res: {
                VersionInfo: {},
                Environment: {},
                DbConfig: {},
                InstallLock: {},
                CreateRsa: {}
            }
        }
    },
    mounted() {
        //MDUI组
        this.mdui = {};
        this.mdui.tab = new mdui.Tab('#tab');
        this.mdui.dialog = new mdui.Dialog('#dialog');
        this.mdui.$ = mdui.$;

        this.getGetVersionInfo();
        this.getCheckEnvironment();
    },
    computed: {

    },
    methods: {
        //内置方法
        checkAllKeysTrue(obj, targetKey) {
            // 遍历对象的每个属性
            for (const key in obj) {
                // 检查属性是否是对象且具有指定的键
                if (typeof obj[key] === 'object' && targetKey in obj[key]) {
                    // 如果找到了指定键且值不为 true，则返回 false
                    if (!obj[key][targetKey]) {
                        return false;
                    }
                }
            }

            // 如果遍历完成所有子对象的指定键都为 true，则返回 true
            return true;
        },
        //绑定
        tabNext() {
            const result = this.checkNext();
            if (result === true) {
                this.tab.activeIndex++;
                setTimeout(() => {
                    this.mdui.tab.next();
                    this.tab.activeIndex = this.mdui.tab.activeIndex;
                    this.tab.nowTabText = this.mdui.$(this.mdui.tab.$tabs[this.tab.activeIndex]).text();
                }, 1);
            }
        },
        tabPrev() {
            this.backPrev();
            this.tab.activeIndex--;
            setTimeout(() => {
                this.mdui.tab.prev();
                this.tab.activeIndex = this.mdui.tab.activeIndex;
                this.tab.nowTabText = this.mdui.$(this.mdui.tab.$tabs[this.tab.activeIndex]).text();
            }, 1);
        },
        backPrev() {
            if (this.tab.activeIndex === 2) {
                this.step.Environment = false;
                this.tab.btnText = '再次检查';
            } else if (this.tab.activeIndex === 3) {
                this.step.CreateRsa = false;
                this.tab.btnText = '配置并导入';
            } else if (this.tab.activeIndex === 4) {
                // this.step.CreateRsa  = false;
                // this.tab.btnText = '创建';
            } else {
                //
            }
        },
        checkNext(isPassOrForce) {
            if (this.tab.activeIndex === 0) {
                if (!this.checkAllKeysTrue(this.res.Environment, 'status')) {
                    this.tab.btnText = "再次检查";
                } else {
                    this.step.Environment = true;
                }
                return true;
            } else if (this.tab.activeIndex === 1) {
                if (!this.step.Environment) {
                    //不满足要求再次发起请求并检查步骤
                    this.getCheckEnvironment().then(() => {
                        if (this.checkAllKeysTrue(this.res.Environment, 'status')) {
                            this.tab.btnText = "下一步";
                            this.step.Environment = true;
                        }
                    });
                    return false;
                } else {
                    this.tab.btnText = "配置并导入";
                    return true;
                }
            } else if (this.tab.activeIndex === 2) {
                if (this.step.DbConfig === false) {
                    //再次尝试
                    this.postDbConfig();
                    return false;
                } else if (this.step.DbConfig === 201) {
                    if (isPassOrForce === 0) {
                        //跳过导入
                        this.req.DbConfig.pass = true;
                        this.postDbConfig().then(() => {
                            this.mdui.dialog.close();
                            this.req.DbConfig.pass = '';
                        });
                        return false;
                    } else if (isPassOrForce === 1) {
                        //强制导入
                        this.req.DbConfig.force = true;
                        this.postDbConfig().then(() => {
                            this.mdui.dialog.close();
                            this.req.DbConfig.force = '';
                        });
                        return false;
                    } else {
                        //弹出选择
                        this.mdui.dialog.open();
                        return false;
                    }
                } else {
                    //成功下一步
                    this.tab.btnText = "创建";
                    return true;
                }
            } else if (this.tab.activeIndex === 3) {
                if (this.step.CreateRsa === false) {
                    this.postCreateRsa();
                    return false;
                } else {
                    //倒数第二步结束同时激活最终按钮
                    this.tab.endBtnShow = true;
                    return true;
                }
            } else if (this.tab.activeIndex === 4) {
                this.setInstallLock().then(() => {
                    BaseEntity.commonFunctions.snackbar('安装锁创建成功，正在前往首页');
                    BaseEntity.JumpUrl('/');
                })
            } else {
                false;
            }
        },
        //API方法
        getGetVersionInfo() {
            return BaseEntity.RequestApiUrl('get', 'SystemInstallGetVersionInfo', {
                inti: () => { },
                then: (res) => {
                    this.res.VersionInfo = res.data;
                    BaseEntity.commonFunctions.snackbar('系统版本信息已获取');
                }
            });
        },
        getCheckEnvironment() {
            return BaseEntity.RequestApiUrl('get', 'SystemInstallCheckEnvironment', {
                inti: () => { },
                then: (res) => {
                    this.res.Environment = res.data;
                    BaseEntity.commonFunctions.snackbar('系统环境信息已获取');
                }
            });
        },
        setInstallLock() {
            return BaseEntity.RequestApiUrl('post', 'SystemInstallSetInstallLock');
        },
        postCreateRsa() {
            return BaseEntity.RequestApiUrl('post', 'SystemInstallCreateRsa', {
                inti: () => { },
                then: (res) => {
                    this.step.CreateRsa = true;
                    this.tab.btnText = "下一步";
                    BaseEntity.commonFunctions.snackbar('RSA密钥对创建成功');
                }
            }, this.req.CreateRsa);
        },
        postDbConfig() {
            return BaseEntity.RequestApiUrl('post', 'SystemInstallSetDbConfig', {
                inti: () => { },
                then: (res) => {
                    //console.log(res.status);
                    if (res.status === 201) {
                        this.step.DbConfig = 201;
                        BaseEntity.commonFunctions.snackbar('数据库已存在');
                    } else {
                        this.step.DbConfig = true;
                        this.tab.btnText = "下一步";
                        BaseEntity.commonFunctions.snackbar('数据库设置成功');
                    }
                }
            }, this.req.DbConfig);
        },
    }
}).mount('#app');