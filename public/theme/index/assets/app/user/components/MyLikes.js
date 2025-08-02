const BaseEntity = new Base();

const MyLikes = {
    data() {
        return {
            theme: { config: { ThemePrimaryDepth: {} } },
            likes: {},
        }
    },
    created() {
        this.getThemeConfig();
        this.getLikes();
        // mdui.mutation();
    },
    methods: {
        goCard(id) {
            BaseEntity.JumpUrl('/index/cards/card/id/' + id);
        },
        previousPage() {
            if (this.likes.current_page == 1) {
                return;
            }
            this.getLikes({ page: this.likes.current_page - 1 });
        },
        nextPage() {
            if (this.likes.current_page == this.likes.last_page) {
                BaseEntity.commonFunctions.snackbar('最后一页啦');
                return;
            }
            this.getLikes({ page: this.likes.current_page + 1 });
        },
        getThemeConfig() {
            BaseEntity.RequestApiUrl('get', 'ThemeConfig', undefined, [], 'UserTokenName').then((res) => {
                this.theme = res.data;
            }).catch((err) => {
                BaseEntity.commonFunctions.snackbar('主题配置获取失败，请刷新页面后再试！');
            })
        },
        getLikes(param = []) {
            BaseEntity.RequestApiUrl('get', 'Likes', undefined, param, 'UserTokenName').then((result) => {
                this.likes = result.data;
                //console.log(this.likes);
            }).catch((err) => {
                BaseEntity.AxiosErrorHandling(err);
            });
        },
        deleteLikes(index) {
            BaseEntity.RequestApiUrl('delete', 'Like', undefined, { id: this.likes.data[index].id }, 'UserTokenName').then((result) => {
                this.likes.data[index].ip = '';
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
                    我的喜欢
                </div>
            </div>
        </div>

        <div class="mdui-row-xs-1 mdui-row-sm-2">

            <div v-for="(item, index) in likes.data" class="mdui-col mdui-m-b-2">
                <div class="mdui-card" v-if="item.card">
                    <!-- 卡片头部，包含头像、标题、副标题 -->
                    <div class="mdui-p-t-2 mdui-p-x-2">
                        
                        <div class="css-caard-header-title" :class="theme.config.ThemePrimaryDepth ? 'mdui-text-color-theme-'+theme.config.ThemePrimaryDepth : 'mdui-text-color-theme'">
                            {{item.card.woName}}的{{item.card.model ? '交流卡' : '表白卡'}}
                            <i class="mdui-icon material-icons mdui-float-right">face</i>
                        </div>
                        <div class="css-caard-header-subtite">{{item.card.woName}}{{item.card.model ? '对' : '表白'}} {{item.card.taName}} {{item.card.model ? '说' : ''}}
                            <span class="css-caard-header-subtite-liulan">浏览{{item.card.look}}</span>
                        </div>
                    </div>

                    <div v-if="item.card.img">
                        <div style="z-index: 1;" class="mdui-card-media mdui-p-t-2">
                            <div class="css-cards-img mdui-m-x-1" style="cursor:pointer;" @click='goCard(item.card.id)'>
                                <img :src="item.card.img" @load="masonry()"/>
                            </div>
                        </div>
                        <div class="css-cards-img-loading mdui-spinner"></div>
                    </div>

                    <!-- 卡片的内容 -->
                    <div class="mdui-p-a-2 mdui-typo">
                        <div class="mdui-p-t-1 css-cards-primary-content" style="cursor:pointer;" @click='goCard(item.card.id)'>
                            {{item.card.content}}
                        </div>
                    </div>

                    <!-- 卡片的按钮 -->
                    <div class="mdui-card-actions">
                        <button class="mdui-btn mdui-float-right" @click="deleteLikes(index)" :disabled="!item.ip">
                            <i class="mdui-icon material-icons">favorite</i> {{item.ip?'取消喜欢':'已取消'}}
                        </button>
                    </div>
                </div>
                <div class="mdui-card" v-if="!item.card">
                    <!-- 卡片的内容 -->
                    <div class="mdui-p-a-2 mdui-typo">
                        <div class="mdui-typo-headline-opacity mdui-text-center">已失效</div>
                    </div>

                    <!-- 卡片的按钮 -->
                    <div class="mdui-card-actions">
                        <button class="mdui-btn mdui-float-right" @click="deleteLikes(index)" :disabled="!item.ip">
                            <i class="mdui-icon material-icons">favorite</i> {{item.ip?'取消喜欢':'已取消'}}
                        </button>
                    </div>
                </div>
            </div>

        </div>
        <div class="mdui-p-t-2 mdui-text-center" v-if="likes.last_page > 1">
            <button type="button" class="mdui-btn mdui-color-theme-accent" @click='previousPage' :disabled="likes.current_page == 1" >
                <i class="mdui-icon material-icons">first_page</i>
            </button>
            <button type="button" class="mdui-btn mdui-color-theme-accent" @click='nextPage()'>
                <i class="mdui-icon material-icons">last_page</i>
            </button>
        </div>
    `,
};

export default MyLikes;