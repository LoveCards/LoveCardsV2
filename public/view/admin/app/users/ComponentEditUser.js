const BaseEntity = new Base();

const DialogEditUser = {
    props: {
        enterUserObj: Object,
        showStatus: Boolean
    },
    data() {
        return {
            outUserObj: { password: '' },
            dialogObj: Object
        }
    },
    computed: {
        dialogObjState() {
            //计算属性获取弹窗对象的展示状态给监听器用
            //console.log(this.dialogObj.state);
            return this.dialogObj.state;
        }
    },
    watch: {
        enterUserObj: function (newValue) {
            //监听父组件传入的用户对象并实时更新
            this.outUserObj = {
                ...newValue
            };
        },
        showStatus: function (newValue) {
            //监听父组件传入的弹窗状态
            if (newValue) {
                this.dialogObj.open();
            } else {
                this.dialogObj.close();
            }
        },
        dialogObjState: function (newValue) {
            //监听组件内部的弹窗状态并传回父组件双向绑定的变量
            //console.log('instState 变化了', newValue);
            if (newValue == 'closing' || newValue == 'closed') {
                this.$emit('update:showStatus', false);
            }
        }

    },
    mounted() {
        //调用MD创建弹窗对象
        this.dialogObj = new mdui.Dialog('#dialog');
    },
    methods: {
        putUser() {
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
            let result = removeCommonProperties(this.outUserObj, this.enterUserObj);
            if (Object.keys(result).length == 0) {
                BaseEntity.commonFunctions.snackbar('请修改后提交');
                return;
            }

            result = { ...result, id: this.enterUserObj.id };
            BaseEntity.RequestApiUrl('patch', 'UsersPatch', undefined, result).then((result) => {
                this.$emit('update:showStatus', false);
                BaseEntity.commonFunctions.snackbar('编辑成功');
                this.$parent.getUsersIndex(this.$parent.UsersIndex.current_page);
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
                uid: this.enterUserObj.id,
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
            }, data).then((result) => {
                console.log(result);
                this.outUserObj.avatar = result.data;
            })
        },
    },
    template: `
    <div class="mdui-dialog" id="dialog">
        <div class="mdui-dialog-title">编辑用户</div>
        <div class="mdui-dialog-content">
            <div class="mdui-row">
                <input type="file" ref="fileInput" style="display: none;" @change="handleFileUpload" />
                <div class="mdui-center mdui-img-circle mdui-ripple mdui-btn-raised" style="height: 180px; max-width: 180px; position: relative;" @click="chooseFile">
                    <img class="mdui-img-fluid" v-bind:src="outUserObj.avatar ? outUserObj.avatar : '/view/admin/assets/img/avatar.png' " style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    <i class="mdui-icon material-icons" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: rgba(255, 255, 255, 0.5); font-size: 32px;">photo_camera</i>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">封禁状态</label>
                        <select style="width: 100%;" class="mdui-select" v-model="outUserObj.status">
                            <option value="0">正常</option>
                            <option value="1">封禁</option>
                        </select>
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">账号</label>
                        <input class="mdui-textfield-input" type="text" v-model="outUserObj.number"/>
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">用户名</label>
                        <input class="mdui-textfield-input" type="text" v-model="outUserObj.username"/>
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">Email</label>
                        <input class="mdui-textfield-input" type="email" v-model="outUserObj.email"/>
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">手机号</label>
                        <input class="mdui-textfield-input" type="text" v-model="outUserObj.phone"/>
                    </div>
                </div>
                <div class="mdui-col-xs-12 mdui-col-md-6">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">密码</label>
                        <input class="mdui-textfield-input" type="password" v-model="outUserObj.password"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="mdui-dialog-actions">
            <button class="mdui-btn mdui-ripple" @click="this.$emit('update:showStatus', false);">取消</button>
            <button class="mdui-btn mdui-ripple" @click="putUser()">提交</button>
        </div>
    </div>
    `,
};

export default DialogEditUser;