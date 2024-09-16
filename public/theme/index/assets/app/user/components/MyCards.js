const BaseEntity = new Base();

const MyCards = {
    data() {
        return {
            theme: { config: { ThemePrimaryDepth: {} } },
            cards: {},
        }
    },
    created() {
        this.getThemeConfig();
        this.getCards();
        mdui.mutation();
    },
    methods: {
        goCard(id) {
            BaseEntity.JumpUrl('/index/Cards/card/id/' + id);
        },
        previousPage() {
            if (this.cards.current_page == 1) {
                return;
            }
            this.getCards({ page: this.cards.current_page - 1 });
        },
        nextPage() {
            if (this.cards.current_page == this.cards.last_page) {
                BaseEntity.commonFunctions.snackbar('最后一页啦');
                return;
            }
            this.getCards({ page: this.cards.current_page + 1 });
        },
        getThemeConfig() {
            BaseEntity.RequestApiUrl('get', 'ThemeConfig', undefined, [], 'UserTokenName').then((res) => {
                this.theme = res.data;
            }).catch((err) => {
                BaseEntity.commonFunctions.snackbar('主题配置获取失败，请刷新页面后再试！');
            })
        },
        getCards(param = []) {
            BaseEntity.RequestApiUrl('get', 'Cards', undefined, param, 'UserTokenName').then((result) => {
                this.cards = result.data;
                //console.log(this.cards);
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
            });
        },
        deleteCards(index) {
            BaseEntity.RequestApiUrl('delete', 'Cards', undefined, { id: this.cards.data[index].id }, 'UserTokenName').then((result) => {
                this.cards.data[index].status = 1;
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
            });
        }
    },
    template: `
        <div class="mdui-card mdui-m-b-2">
            <div class="mdui-p-a-2">
                <div class="mdui-typo-title-opacity">
                    我的卡片
                </div>
            </div>
        </div>

        <div class="mdui-row-xs-1 mdui-row-sm-2">

            <div v-for="(item, index) in cards.data" class="mdui-col mdui-m-b-2">
                <div class="mdui-card">
                    <!-- 卡片头部，包含头像、标题、副标题 -->
                    <div class="mdui-p-t-2 mdui-p-x-2">
                        
                        <div class="css-caard-header-title" :class="theme.config.ThemePrimaryDepth ? 'mdui-text-color-theme-'+theme.config.ThemePrimaryDepth : 'mdui-text-color-theme'">
                            {{item.woName}}的{{item.model ? '交流卡' : '表白卡'}}
                            <i class="mdui-icon material-icons mdui-float-right">face</i>
                        </div>
                        <div class="css-caard-header-subtite">{{item.woName}}{{item.model ? '对' : '表白'}} {{item.taName}} {{item.model ? '说' : ''}}
                            <span class="css-caard-header-subtite-liulan">浏览{{item.look}}</span>
                        </div>
                    </div>

                    <div v-if="item.img">
                        <div style="z-index: 1;" class="mdui-card-media mdui-p-t-2">
                            <div class="css-cards-img mdui-m-x-1" style="cursor:pointer;" @click='goCard(item.id)'>
                                <img :src="item.img" />
                            </div>
                        </div>
                        <div class="css-cards-img-loading mdui-spinner"></div>
                    </div>

                    <!-- 卡片的内容 -->
                    <div class="mdui-p-a-2 mdui-typo">
                        <div class="mdui-p-t-1 css-cards-primary-content" style="cursor:pointer;" @click='goCard(item.id)'>
                            {{item.content}}
                        </div>
                    </div>

                    <!-- 卡片的按钮 -->
                    <div class="mdui-card-actions">
                        <button class="mdui-btn mdui-float-right" @click="deleteCards(index)" :disabled="item.status == 1">
                            <i class="mdui-icon material-icons">delete</i> {{item.status == 1?'已删除':'删除'}}
                        </button>
                    </div>
                </div>
            </div>

        </div>
        <div class="mdui-p-t-2 mdui-text-center" v-if="cards.last_page > 1">
            <button type="button" class="mdui-btn mdui-color-theme-accent" @click='previousPage' :disabled="cards.current_page == 1" >
                <i class="mdui-icon material-icons">first_page</i>
            </button>
            <button type="button" class="mdui-btn mdui-color-theme-accent" @click='nextPage()'>
                <i class="mdui-icon material-icons">last_page</i>
            </button>
        </div>
    `,
};

export default MyCards;