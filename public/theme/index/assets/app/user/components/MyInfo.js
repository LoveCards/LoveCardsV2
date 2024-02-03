const BaseEntity = new Base();

const MyInfo = {
    data() {
        return {
            copyUserInfo: {},
            userInfo: {},
            email: {
                newEmail: '',
                captcha: ''
            },
            sendMsgBtn: {
                disabled: false,
                countdown: 60,
                showText: '',
            }
        }
    },
    created() {
        this.getUserInfo();
        mdui.mutation();
    },
    methods: {
        startCountdown() {
            const countdown = this.sendMsgBtn.countdown;
            let timer = setInterval(() => {
                if (this.sendMsgBtn.countdown > 0) {
                    this.sendMsgBtn.countdown--;
                    this.sendMsgBtn.showText = '剩余' + this.sendMsgBtn.countdown + '秒';
                } else {
                    // 倒计时结束后的处理逻辑
                    clearInterval(timer);
                    this.sendMsgBtn = {
                        disabled: false,
                        countdown: countdown,
                        showText: '',
                    }
                }
            }, 1000); // 每秒更新一次
        },
        postUserEmail() {
            BaseEntity.RequestApiUrl('Post', 'UserEmail', {
                inti: () => { },
                then: () => {
                    BaseEntity.commonFunctions.snackbar('修改成功');
                    this.getUserInfo();
                },
                catch: (err) => {
                    BaseEntity.AxiosErrorHandling(err);
                }
            }, { 'email': this.email.newEmail, 'captcha': this.email.captcha }, 'UserTokenName');
        },
        getUserEmailCaptcha() {
            if (!this.sendMsgBtn.disabled) {
                BaseEntity.RequestApiUrl('Post', 'UserEmailCaptcha', {
                    inti: () => {
                        BaseEntity.commonFunctions.snackbar('正在发送请稍后');
                        this.sendMsgBtn.disabled = true;
                    },
                    then: () => {
                        BaseEntity.commonFunctions.snackbar('发送成功，请注意查收');
                        this.startCountdown();
                    },
                    catch: (err) => {
                        BaseEntity.AxiosErrorHandling(err);
                        this.sendMsgBtn.disabled = false;
                    }
                }, { 'email': this.email.newEmail }, 'UserTokenName');
            }
        },
        getUserInfo() {
            BaseEntity.RequestApiUrl('get', 'UserInfo', undefined, [], 'UserTokenName').then((result) => {
                this.userInfo = { ...result.data };
                this.copyUserInfo = { ...result.data };
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
            });
        },
        patchUserInfo() {
            function removeCommonProperties(obj1, obj2) {
                //比对原始对象去除空属性与相同属性
                const result = { ...obj1 };
                for (const key in result) {
                    if (obj2.hasOwnProperty(key) && result[key] === obj2[key]) {
                        delete result[key];
                    }
                }
                return result;
            }

            let result = removeCommonProperties(this.userInfo, this.copyUserInfo);
            if (Object.keys(result).length == 0) {
                BaseEntity.commonFunctions.snackbar('请修改后提交');
                return;
            }

            result = { ...result, id: this.copyUserInfo.id };
            BaseEntity.RequestApiUrl('patch', 'UserInfo', undefined, result, 'UserTokenName').then((result) => {
                this.$emit('update:showStatus', false);
                BaseEntity.commonFunctions.snackbar('修改成功');
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
            });
        },
        chooseFile() {
            this.$refs.fileInput.click();
        },
        handleFileUpload(event) {
            const file = event.target.files[0];
            this.uploadUserImages(file);
        },
        uploadUserImages(file) {
            let data = {
                file: file,
                aid: 0, //用户应用ID
                pid: 0, //临时条目ID
                ReqHeaders: {
                    'Content-Type': 'multipart/form-data'
                }
            };
            BaseEntity.RequestApiUrl('Post', 'UploadUserImages', {
                inti: () => {
                    BaseEntity.commonFunctions.snackbar('正在上传');
                },
                then: () => {
                    BaseEntity.commonFunctions.snackbar('上传成功，提交后保存');
                }
            }, data, 'UserTokenName').then((result) => {
                this.userInfo.avatar = result.data;
            })
        },
    },
    // beforeDestroy() {
    //     // 在组件销毁前清除定时器，防止内存泄漏
    //     clearInterval(this.timer);
    // },
    template: `
    <div class="mdui-card">
        <div class="mdui-p-a-2">
            <div class="mdui-typo-title-opacity">
                个人资料
            </div>
            <div class="mdui-row mdui-m-y-1">
                <input type="file" ref="fileInput" style="display: none;" @change="handleFileUpload" />
                <div class="mdui-center mdui-img-circle mdui-ripple mdui-btn-raised"
                    style="height: 180px; max-width: 180px; position: relative;" @click="chooseFile">
                    <img class="mdui-img-fluid"
                        v-bind:src="userInfo.avatar ? userInfo.avatar : '/view/admin/assets/img/avatar.png' "
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    <i class="mdui-icon material-icons"
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: rgba(255, 255, 255, 0.5); font-size: 32px;">photo_camera</i>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield mdui-textfield-disabled">
                        <label class="mdui-textfield-label">账号</label>
                        <input class="mdui-textfield-input" type="text" v-model="userInfo.number" disabled="true" />
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">用户名</label>
                        <input class="mdui-textfield-input" type="text" v-model="userInfo.username" />
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield mdui-textfield-disabled">
                        <label class="mdui-textfield-label">Email</label>
                        <input class="mdui-textfield-input" type="email" v-model="userInfo.email" disabled="true" />
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield mdui-textfield-disabled">
                        <label class="mdui-textfield-label">手机号</label>
                        <input class="mdui-textfield-input" type="text" v-model="userInfo.phone" disabled="true" />
                    </div>
                </div>
            </div>
            <div class="mdui-m-t-4 mdui-text-right">
                <button class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-theme-accent"
                    @click="patchUserInfo()">保存</button>
            </div>
        </div>
    </div>
    <div class="mdui-card mdui-m-t-2">
        <div class="mdui-p-a-2">
            <div class="mdui-typo-title-opacity">
                修改邮箱
            </div>
            <div class="mdui-m-y-1">
                <div class="mdui-textfield mdui-textfield-floating-label">
                    <label class="mdui-textfield-label">新的邮箱</label>
                    <input v-model="email.newEmail" class="mdui-textfield-input" type="text" />
                </div>
                <div class="mdui-row">
                    <div class="mdui-col-xs-8">
                        <div class="mdui-textfield mdui-textfield-floating-label">
                            <label class="mdui-textfield-label">验证码</label>
                            <input v-model="email.captcha" class="mdui-textfield-input" type="text" />
                        </div>
                    </div>
                    <div class="mdui-col-xs-4">
                        <div class="mdui-valign">
                            <button :disabled="sendMsgBtn.disabled" @click="getUserEmailCaptcha()"
                                style="margin-top: 42px; width: 100%;"
                                class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-pink-accent">
                                {{sendMsgBtn.countdown != 60?sendMsgBtn.showText:'验证码'}}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mdui-m-t-4 mdui-text-right">
                    <button class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-theme-accent"
                        @click="postUserEmail()">保存</button>
                </div>
            </div>
        </div>
    </div>
    `,
};

export default MyInfo;