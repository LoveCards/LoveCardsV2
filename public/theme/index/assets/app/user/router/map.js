// 1. 定义路由组件.
import MyInfo from "../components/MyInfo.js";

// 2. 定义一些路由
const routes = [
    {
        path: '/',
        component: MyInfo,
        meta: {
            title: '个人资料'
        },
    },
]

export default routes;