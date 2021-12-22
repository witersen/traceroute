import Vue from 'vue';
import App from './app.vue';
import Routers from './router';
import Vuex from 'vuex';
import store from './vuex/store';

import ViewUI from 'view-design';
Vue.use(ViewUI);

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import axios from 'axios';
Vue.prototype.$axios = axios;

import 'view-design/dist/styles/iview.css';

/**
 * 路由配置
 */
const RouterConfig = {
    mode: 'history',
    routes: Routers
};

const router = new VueRouter(RouterConfig);

router.beforeEach((to, from, next) => {
    ViewUI.LoadingBar.start();
    next();
});

router.afterEach((to, from, next) => {
    ViewUI.LoadingBar.finish();
    window.scrollTo(0, 0);
});

/**
 * 配置请求拦截器
 */
axios.defaults.baseURL = "/"; //公共url

axios.interceptors.request.use(config => {
    return config
}, error => {
    if (error.response) {
    }
})

/**
 * 配置响应拦截器
 */
axios.interceptors.response.use(response => {
    return response
}, error => {
    // 异常
    return Promise.reject(error)
})

new Vue({
    el: '#app',
    router: router,
    store,
    render: h => h(App)
});
