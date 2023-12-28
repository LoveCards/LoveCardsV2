const { createApp } = Vue;
const BaseEntity = new Base();
import ComponentEditUser from './ComponentEditUser.js';

const app = createApp({
    data() {
        return {
            UsersIndex: {},
            EditUser: {},
            EditShow: false,
        };
    },
    created() {
        this.getUsersIndex();
    },
    methods: {
        /**
         * 删除ID
         * @param {*} id 
         */
        deleteUser(id) {
            BaseEntity.RequestApiUrl('Delete', 'UsersDelete', undefined, { id: id }).then((result) => {
                BaseEntity.commonFunctions.snackbar('删除成功');
                this.getUsersIndex(this.UsersIndex.current_page);
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
            });
        },
        nextPage: function () {
            this.getUsersIndex(this.UsersIndex.current_page + 1);
        },
        prevPage: function () {
            this.getUsersIndex(this.UsersIndex.current_page - 1);
        },
        getUsersIndex: function (page = 1) {
            BaseEntity.RequestApiUrl('get', 'UsersIndex', undefined, { page: page }).then((result) => {
                this.UsersIndex = result.data;
                //console.log(this.UsersIndex);
            }).catch((err) => {
                BaseEntity.commonFunctions.snackbar('UsersIndex请求出错');
                //console.log(err);
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
