// ========================================
// 功能：我的评论
// 挂载点：#user-comments-app
// 数据流：sdk.comments.listOwn()
// ========================================

(function () {
  var el = document.getElementById('user-comments-app');
  if (!el || !window.Vue) return;

  Vue.createApp({
    setup: function () {
      var comments = Vue.ref([]);
      var loading = Vue.ref(true);
      var page = Vue.ref(1);
      var lastPage = Vue.ref(1);

      function load(p) {
        p = p || 1;
        if (!requireAuth()) return;
        loading.value = true;
        sdk.comments.listOwn()
          .then(function (res) {
            if (p === 1) comments.value = res.data || [];
            else comments.value = comments.value.concat(res.data || []);
            lastPage.value = (res.pagination && res.pagination.totalPages) || 1;
            page.value = p;
          })
          .catch(function () { goLogin(); })
          .finally(function () { loading.value = false; });
      }

      Vue.onMounted(function () { load(1); });

      function loadMore() { if (page.value < lastPage.value) load(page.value + 1); }

      return { comments: comments, loading: loading, page: page, lastPage: lastPage, loadMore: loadMore };
    },
    template: '<div>' +
      '<p v-if="loading && comments.length === 0">加载中...</p>' +
      '<p v-else-if="comments.length === 0" class="muted">暂无评论</p>' +
      '<div v-for="c in comments" :key="c.id" class="comment">' +
      '<p>{{ c.content }}</p>' +
      '<small><a :href="\'/cards/\' + c.aid">查看卡片</a> · {{ c.created_at }}</small></div>' +
      '<p v-if="page < lastPage"><a href="#" @click.prevent="loadMore">加载更多</a></p>' +
      '<p><a href="/user">← 返回个人中心</a></p></div>'
  }).mount(el);
})();
