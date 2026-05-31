// ========================================
// 功能：卡片详情 — 评论 + 点赞 + 编辑/删除
// 挂载点：#card-app
// 数据流：sdk.comments.create() / sdk.cards.like() / sdk.cards.delete()
// 数据依赖：data-card-id / data-card-user-id 属性
// ========================================

(function () {
  var el = document.getElementById('card-app');
  if (!el || !window.Vue) return;

  var cardId = Number(el.dataset.cardId);
  var cardUserId = Number(el.dataset.cardUserId || 0);
  var currentUserId = window.getTokenUserId();

  Vue.createApp({
    setup: function () {
      var content = Vue.ref('');
      var loading = Vue.ref(false);
      var liked = Vue.ref(false);
      var isOwner = Vue.ref(currentUserId && cardUserId && currentUserId === cardUserId);

      Vue.onMounted(function () {
        if (!currentUserId) return;
        sdk.likes.list()
          .then(function (res) {
            var items = res.data || [];
            liked.value = items.some(function (item) {
              return (item.card_id || item.sid) === cardId;
            });
          })
          .catch(function () {});
      });

      function submitComment() {
        if (!content.value.trim()) return;
        if (!requireAuth()) return;
        loading.value = true;
        sdk.comments.create(cardId, { content: content.value })
          .then(function () { location.reload(); })
          .catch(function () {})
          .finally(function () { loading.value = false; });
      }

      function toggleLike() {
        if (!requireAuth()) return;
        sdk.cards.like(cardId)
          .then(function () { liked.value = true; })
          .catch(function () {});
      }

      function deleteCard() {
        if (!requireAuth()) return;
        if (!confirm('确定删除这张卡片？')) return;
        loading.value = true;
        sdk.cards.delete(cardId)
          .then(function () { location.href = '/cards'; })
          .catch(function () { loading.value = false; });
      }

      return {
        content: content, loading: loading, liked: liked, isOwner: isOwner, cardId: cardId,
        submitComment: submitComment, toggleLike: toggleLike, deleteCard: deleteCard
      };
    },
    template: '<div style="margin-top:1.5rem">' +
      '<div v-if="isOwner" style="margin-bottom:1rem;display:flex;gap:0.5rem">' +
      '<a :href="\'/publish?edit=\' + cardId" role="button" class="secondary outline" style="margin:0">编辑</a>' +
      '<button @click="deleteCard" class="secondary outline" style="margin:0">删除</button></div>' +
      '<button @click="toggleLike" :class="{liked:liked}" class="like-btn" style="margin-bottom:1rem">' +
      '{{ liked ? \'已赞\' : \'点赞\' }}</button>' +
      '<textarea v-model="content" placeholder="写评论..." rows="3"></textarea>' +
      '<button @click="submitComment" :disabled="loading" style="margin-top:0.5rem">' +
      '{{ loading ? \'发送中...\' : \'发表评论\' }}</button></div>'
  }).mount(el);
})();
