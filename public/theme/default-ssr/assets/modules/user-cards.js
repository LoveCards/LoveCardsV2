// ========================================
// 功能：我的卡片
// 挂载点：#user-cards-app
// 数据流：sdk.cards.listOwn() / sdk.cards.delete()
// ========================================

(function () {
  var el = document.getElementById('user-cards-app');
  if (!el || !window.Vue) return;

  Vue.createApp({
    setup: function () {
      var cards = Vue.ref([]);
      var loading = Vue.ref(true);
      var page = Vue.ref(1);
      var lastPage = Vue.ref(1);

      function load(p) {
        p = p || 1;
        if (!requireAuth()) return;
        loading.value = true;
        sdk.cards.listOwn()
          .then(function (res) {
            if (p === 1) cards.value = res.data || [];
            else cards.value = cards.value.concat(res.data || []);
            lastPage.value = (res.pagination && res.pagination.totalPages) || 1;
            page.value = p;
          })
          .catch(function () { goLogin(); })
          .finally(function () { loading.value = false; });
      }

      Vue.onMounted(function () { load(1); });

      function loadMore() {
        if (page.value < lastPage.value) load(page.value + 1);
      }

      function deleteCard(id) {
        if (!confirm('确定删除这张卡片？')) return;
        sdk.cards.delete(id)
          .then(function () {
            cards.value = cards.value.filter(function (c) { return c.id !== id; });
          })
          .catch(function () { showToast('删除失败', 'error'); });
      }

      return { cards: cards, loading: loading, page: page, lastPage: lastPage, loadMore: loadMore, deleteCard: deleteCard };
    },
    template: '<div>' +
      '<p v-if="loading && cards.length === 0">加载中...</p>' +
      '<p v-else-if="cards.length === 0" class="muted">暂无卡片，<a href="/publish">去发布</a></p>' +
      '<article v-for="card in cards" :key="card.id" class="card-item">' +
      '<header><a :href="\'/cards/\' + card.id"><strong>{{ (card.data && card.data.title) || \'无标题\' }}</strong></a></header>' +
      '<p>{{ (card.content || \'\').substring(0, 100) }}</p>' +
      '<footer><small>❤ {{ card.goods }} · 👁 {{ card.views }} · 💬 {{ card.comments }}</small>' +
      '<button @click="deleteCard(card.id)" class="secondary outline" style="float:right;margin:0;padding:0.2rem 0.5rem;font-size:0.8rem">删除</button>' +
      '</footer></article>' +
      '<p v-if="loading && cards.length > 0">加载中...</p>' +
      '<p v-if="page < lastPage"><a href="#" @click.prevent="loadMore">加载更多</a></p>' +
      '<p><a href="/user">← 返回个人中心</a></p></div>'
  }).mount(el);
})();
