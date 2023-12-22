const { createApp } = Vue;
const BaseEntity = new Base();

const ComponentEditUser = {
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
        //提交方法
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
            result = { ...result, id: this.enterUserObj.id };
            BaseEntity.RequestApiUrl('patch', 'UsersPatch', undefined, result).then((result) => {
                this.$emit('update:showStatus', false);
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
                //console.log(err);
            });
        }

    },
    template: `
    <div class="mdui-dialog" id="dialog">
        <div class="mdui-dialog-title">编辑用户</div>
        <div class="mdui-dialog-content">
            <div class="mdui-row">
                <div class="mdui-center mdui-img-circle mdui-ripple mdui-btn-raised" style="height: 180px; max-width: 180px; position: relative;">
                    <img class="mdui-img-fluid" src="https://s3.bmp.ovh/imgs/2023/12/20/006e9f8cb0adb3fd.png" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
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

const app = createApp({
    data() {
        return {
            UsersIndex: null,
            EditUser: null,
            EditShow: false,
        };
    },
    created() {
        this.getUsersIndex();
    },
    methods: {
        getUsersIndex: function () {
            BaseEntity.RequestApiUrl('get', 'UsersIndex').then((result) => {
                this.UsersIndex = result.data.data;
            }).catch((err) => {
                console.log(err);
            });
        },
        editUser: function (userData) {
            console.log(userData);
            // 在调用editUser方法时，通过v-model更新EditShow属性，触发弹窗显示
            this.EditShow = true;
            this.EditUser = userData;
        }
    },
    components: {
        'component-edit-user': ComponentEditUser,
    }
}).mount('#app');
