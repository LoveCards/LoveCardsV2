// ========================================
// 功能：搜索 + 分页
// 挂载点：#search-app
// 数据流：sdk.cards.search()
// ========================================

(function () {
  var el = document.getElementById('search-app');
  if (!el || !window.Vue) return;

  Vue.createApp({
    setup: function () {
      var keyword = Vue.ref('');
      var results = Vue.ref([]);
      var searching = Vue.ref(false);
      var searched = Vue.ref(false);
      var page = Vue.ref(1);
      var lastPage = Vue.ref(1);

      function doSearch(p) {
        if (!keyword.value.trim()) return;
        p = p || 1;
        searching.value = true;
        searched.value = true;
        page.value = p;
        sdk.cards.search({ keyword: keyword.value, page: p })
          .then(function (res) {
            results.value = res.data || [];
            lastPage.value = (res.pagination && res.pagination.totalPages) || 1;
          })
          .catch(function () {})
          .finally(function () { searching.value = false; });
      }

      function nextPage() {
        if (page.value < lastPage.value) doSearch(page.value + 1);
      }

      function prevPage() {
        if (page.value > 1) doSearch(page.value - 1);
      }

      return {
        keyword: keyword, results: results, searching: searching,
        searched: searched, page: page, lastPage: lastPage,
        doSearch: doSearch, nextPage: nextPage, prevPage: prevPage
      };
    },
    template: '<div>' +
      '<div class="search-bar"><input v-model="keyword" @keyup.enter="doSearch()" placeholder="输入关键词搜索卡片..." />' +
      '<button @click="doSearch()" :disabled="searching">搜索</button></div>' +
      '<p v-if="!searched" class="muted">输入关键词开始搜索</p>' +
      '<p v-else-if="searching">搜索中...</p>' +
      '<p v-else-if="results.length === 0" class="muted">未找到相关卡片</p>' +
      '<article v-for="card in results" :key="card.id" class="card-item">' +
      '<header><a :href="\'/cards/\' + card.id"><strong>{{ (card.data && card.data.title) || \'无标题\' }}</strong></a></header>' +
      '<p>{{ (card.content || \'\').substring(0, 100) }}</p></article>' +
      '<nav v-if="searched && lastPage > 1" class="pagination">' +
      '<a href="#" @click.prevent="prevPage" :class="{disabled:page<=1}">上一页</a>' +
      '<span style="padding:0.3rem 0.75rem">{{ page }} / {{ lastPage }}</span>' +
      '<a href="#" @click.prevent="nextPage" :class="{disabled:page>=lastPage}">下一页</a></nav></div>'
  }).mount(el);
})();
