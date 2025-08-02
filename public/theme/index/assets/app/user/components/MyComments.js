const BaseEntity = new Base();

const MyComments = {
    data() {
        return {
            theme: { config: { ThemePrimaryDepth: {} } },
            comments: {},
        };
    },
    created() {
        this.getThemeConfig();
        this.getComments();
        mdui.mutation();
    },
    methods: {
        // goCard(id) {
        //     BaseEntity.JumpUrl("/index/Cards/card/id/" + id);
        // },
        viewStatus(status) {
            if (status == 0) {
                return "正常";
            } else if (status == 1) {
                return "封禁";
            } else if (status == 3) {
                return "待审核";
            }
            return "未知状态";
        },
        previousPage() {
            if (this.comments.current_page == 1) {
                return;
            }
            this.getComments({ page: this.comments.current_page - 1 });
        },
        nextPage() {
            if (this.comments.current_page == this.comments.last_page) {
                BaseEntity.commonFunctions.snackbar("最后一页啦");
                return;
            }
            this.getComments({ page: this.comments.current_page + 1 });
        },
        getThemeConfig() {
            BaseEntity.RequestApiUrl(
                "get",
                "ThemeConfig",
                undefined,
                [],
                "UserTokenName"
            )
                .then((res) => {
                    this.theme = res.data;
                })
                .catch((err) => {
                    BaseEntity.commonFunctions.snackbar(
                        "主题配置获取失败，请刷新页面后再试！"
                    );
                });
        },
        getComments(param = []) {
            BaseEntity.RequestApiUrl(
                "get",
                "Comments",
                undefined,
                param,
                "UserTokenName"
            )
                .then((result) => {
                    this.comments = result.data;
                    //console.log(this.cards);
                })
                .catch((err) => {
                    BaseEntity.AxiosErrorHandling(err);
                });
        },
        deleteComments(index) {
            BaseEntity.RequestApiUrl(
                "delete",
                "Comment",
                undefined,
                { id: this.comments.data[index].id },
                "UserTokenName"
            )
                .then((result) => {
                    this.comments.data[index].status = 1;
                })
                .catch((err) => {
                    BaseEntity.AxiosErrorHandling(err);
                });
        },
    },
    template: `
        <div class="mdui-card mdui-m-b-2">
            <div class="mdui-p-a-2">
                <div class="mdui-typo-title-opacity">
                    我的评论
                </div>
            </div>
        </div>

        <div class="mdui-card mdui-m-t-1" v-if="comments.total">
            <!-- 卡片的内容 -->
            <ul class="mdui-list">
                
                <template v-for="(item, index) in comments.data" :key="index">
                    <li class="css-list-item">
                        <div class="mdui-list-item-content">
                            <div class="mdui-list-item-title">{{item.content}}</div>
                            <div class="mdui-list-item-text">#{{item.id}} {{item.created_at}} {{viewStatus(item.status)}}</div>
                        </div>
                        <button v-if="item.status == 0" class="mdui-btn mdui-btn-icon mdui-list-item-secondary" @click="deleteComments(index)" :disabled="item.status == 1">
                            <i class="mdui-icon material-icons">delete</i>
                        </button>
                    </li>
                    <li class="mdui-divider" v-if="comments.total != index+1"></li>
                </template>

            </ul>
        </div>

        <div class="mdui-p-t-2 mdui-text-center" v-if="comments.last_page > 1">
            <button type="button" class="mdui-btn mdui-color-theme-accent" @click='previousPage' :disabled="cards.current_page == 1" >
                <i class="mdui-icon material-icons">first_page</i>
            </button>
            <button type="button" class="mdui-btn mdui-color-theme-accent" @click='nextPage()'>
                <i class="mdui-icon material-icons">last_page</i>
            </button>
        </div>
    `,
};

export default MyComments;
