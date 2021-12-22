const publicRoutes = [
    {
        name: 'index',
        path: '/',
        meta: {
            requireAuth: false,
        },
        component: (resolve) => require(['./views/index.vue'], resolve)
    },

];

export default publicRoutes;
