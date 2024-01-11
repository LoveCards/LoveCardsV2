import routes from './map.js';

// 3. 创建路由实例并传递 `routes` 配置
const router = VueRouter.createRouter({
    // 4. 内部提供了 history 模式的实现。为了简单起见，我们在这里使用 hash 模式。
    history: VueRouter.createWebHashHistory(),
    routes, // `routes: routes` 的缩写
})

// 在导航守卫中设置页面标题
router.beforeEach((to, from, next) => {
    document.title = to.meta.title || '用户中心 - LoveCards';
    next();
});

export default router