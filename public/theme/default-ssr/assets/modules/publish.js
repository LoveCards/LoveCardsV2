// ========================================
// 功能：发布 / 编辑卡片
// 挂载点：#publish-app
// 数据流：sdk.tags.list() / sdk.cards.create() / sdk.cards.update()
// 编辑模式：URL 参数 ?edit=ID → 预填充 → sdk.cards.get(ID)
// ========================================

(function () {
  var el = document.getElementById('publish-app');
  if (!el || !window.Vue) return;

  var editId = (new URL(location.href)).searchParams.get('edit');

  Vue.createApp({
    setup: function () {
      var content = Vue.ref('');
      var title = Vue.ref('');
      var selectedTags = Vue.ref([]);
      var allTags = Vue.ref([]);
      var loading = Vue.ref(false);
      var error = Vue.ref('');
      var isEdit = Vue.ref(!!editId);

      Vue.onMounted(function () {
        if (!requireAuth()) return;

        sdk.tags.list({ list_rows: 100 })
          .then(function (res) { allTags.value = res.data || []; })
          .catch(function () {});

        if (editId) {
          sdk.cards.get(editId)
            .then(function (res) {
              var card = res.data;
              content.value = card.content || '';
              title.value = (card.data && card.data.title) || '';
              if (card.tags) {
                try {
                  selectedTags.value = JSON.parse(
                    typeof card.tags === 'string' ? card.tags : JSON.stringify(card.tags)
                  );
                } catch (e) {}
              }
            })
            .catch(function (e) { error.value = '加载卡片失败: ' + e.message; });
        }
      });

      function toggleTag(id) {
        var idx = selectedTags.value.indexOf(id);
        if (idx >= 0) selectedTags.value.splice(idx, 1);
        else selectedTags.value.push(id);
      }

      function submit() {
        if (!content.value.trim()) { error.value = '内容不能为空'; return; }
        if (!requireAuth()) return;
        error.value = '';
        loading.value = true;

        var params = { content: content.value };
        var data = {};
        if (title.value.trim()) data.title = title.value.trim();
        if (Object.keys(data).length > 0) params.data = data;
        if (selectedTags.value.length > 0) params.tags = JSON.stringify(selectedTags.value);

        var promise;
        if (isEdit.value && editId) {
          promise = sdk.cards.update(editId, params).then(function () { location.href = '/cards/' + editId; });
        } else {
          promise = sdk.cards.create(params).then(function (res) { location.href = '/cards/' + res.data.id; });
        }

        promise
          .catch(function (e) { error.value = e.message; })
          .finally(function () { loading.value = false; });
      }

      return {
        content: content, title: title, selectedTags: selectedTags, allTags: allTags,
        loading: loading, error: error, isEdit: isEdit, editId: editId,
        toggleTag: toggleTag, submit: submit
      };
    },
    template: '<div>' +
      '<p v-if="error" style="color:red">{{ error }}</p>' +
      '<input v-model="title" placeholder="标题（可选）" />' +
      '<textarea v-model="content" placeholder="写点什么..." rows="6"></textarea>' +
      '<div v-if="allTags.length > 0" style="margin:1rem 0">' +
      '<p style="margin-bottom:0.5rem">选择标签：</p>' +
      '<div style="display:flex;flex-wrap:wrap;gap:0.5rem">' +
      '<a v-for="tag in allTags" :key="tag.id" href="#" @click.prevent="toggleTag(tag.id)" ' +
      ':class="{tag:true, selected:selectedTags.includes(tag.id)}" ' +
      'style="padding:0.2rem 0.75rem;border:1px solid var(--pico-muted-border-color);border-radius:2rem;cursor:pointer;text-decoration:none"' +
      '>{{ tag.name }}</a></div></div>' +
      '<button @click="submit" :disabled="loading" style="margin-top:1rem">' +
      '{{ loading ? (isEdit ? \'更新中...\' : \'发布中...\') : (isEdit ? \'更新卡片\' : \'发布卡片\') }}</button></div>'
  }).mount(el);
})();
