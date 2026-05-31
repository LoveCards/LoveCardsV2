// ========================================
// 功能：我的点赞
// 挂载点：#user-likes-app
// 数据流：sdk.likes.list() / sdk.likes.unlike()
// ========================================

(function () {
  var el = document.getElementById('user-likes-app');
  if (!el || !window.Vue) return;

  Vue.createApp({
    setup: function () {
      var likes = Vue.ref([]);
      var loading = Vue.ref(true);

      function load() {
        if (!requireAuth()) return;
        loading.value = true;
        sdk.likes.list()
          .then(function (res) {
            likes.value = res.data || [];
          })
          .catch(function () { goLogin(); })
          .finally(function () { loading.value = false; });
      }

      Vue.onMounted(function () { load(); });

      function unlike(id) {
        sdk.likes.unlike(id)
          .then(function () { likes.value = likes.value.filter(function (l) { return l.id !== id; }); })
          .catch(function () { showToast('取消失败', 'error'); });
      }

      return { likes: likes, loading: loading, unlike: unlike };
    },
    template: '<div>' +
      '<p v-if="loading && likes.length === 0">加载中...</p>' +
      '<p v-else-if="likes.length === 0" class="muted">暂无点赞</p>' +
      '<article v-for="like in likes" :key="like.id" class="card-item">' +
      '<p>卡片 #{{ like.sid || like.card_id }} · {{ like.created_at }}</p>' +
      '<button @click="unlike(like.id)" class="secondary outline" style="margin:0;padding:0.2rem 0.5rem;font-size:0.8rem">取消点赞</button>' +
      '</article></div>'
  }).mount(el);
})();
