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
                //console.log(result.data.data);
                result.data.data.map(element => {
                    element.data = JSON.parse(element.data);
                });
                this.cards = result.data;
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
            });
        },
        deleteCards(index) {
            BaseEntity.RequestApiUrl('delete', 'Card', undefined, { id: this.cards.data[index].id }, 'UserTokenName').then((result) => {
                this.cards.data[index].status = 1;
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
            });
        },
        masonry() {
            const grid = document.querySelector('.mdui-row-xs-1');
            //console.log(grid.innerHTML);

            if (grid) {
                new Masonry(grid, {
                    itemSelector: '.mdui-col',
                    columnWidth: '.mdui-col',
                    percentPosition: true
                });
            }
        }
    },
    watch: {
        likes() {
            this.$nextTick(() => {
                this.masonry()
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
                    <div class="mdui-p-t-2 mdui-p-x-2" v-if="item.data">
                        
                        <div class="css-caard-header-title" :class="theme.config.ThemePrimaryDepth ? 'mdui-text-color-theme-'+theme.config.ThemePrimaryDepth : 'mdui-text-color-theme'">
                            {{item.data.woName ? item.data.woName : '匿名'}}的{{item.data.model ? '交流卡' : '表白卡'}}
                            <i class="mdui-icon material-icons mdui-float-right">face</i>
                        </div>
                        <div class="css-caard-header-subtite">
                            #{{item.id}} {{item.created_at}} {{viewStatus(item.status)}}
                            <span class="css-caard-header-subtite-liulan">浏览{{item.views}}</span>
                        </div>
                    </div>

                    <div v-if="item.cover">
                        <div style="z-index: 1;" class="mdui-card-media mdui-p-t-2">
                            <div class="css-cards-img mdui-m-x-1" style="cursor:pointer;" @click='goCard(item.id)'>
                                <img :src="item.cover" @load="masonry()"/>
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
                        <button  v-if="item.status == 0" class="mdui-btn mdui-float-right" @click="deleteCards(index)" :disabled="item.status == 1">
                            <i class="mdui-icon material-icons">delete</i> {{item.status == 1?'已删除':'删除'}}
                        </button>
                        <button  v-if="item.status != 0" class="mdui-btn mdui-float-right" disabled>
                            <i class="mdui-icon material-icons">delete</i> 不可删除
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