// 1. 定义路由组件.
import MyInfo from "../components/MyInfo.js";
import MyCards from "../components/MyCards.js";
import MyComments from "../components/MyComments.js";
// import MyLikes from "../components/MyLikes.js";

// 2. 定义一些路由
const routes = [
    {
        path: '/',
        component: MyInfo,
        meta: {
            title: '个人资料'
        },
    },
    {
        path: '/my-cards',
        component: MyCards,
        meta: {
            title: '我的卡片'
        },
    },
    {
        path: '/my-comments',
        component: MyComments,
        meta: {
            title: '我的评论'
        },
    },
    // {
    //     path: '/my-liks',
    //     component: MyLikes,
    //     meta: {
    //         title: '我的喜欢'
    //     },
    // },
]

export default routes;