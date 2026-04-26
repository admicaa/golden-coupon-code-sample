import Vue from 'vue'
import Router from 'vue-router'
Vue.use(Router)

import { store } from '@/store/store.js'
// components
import Main from '@/container/main.vue';
import Admins from 'Views/backend/admins.vue';
import Articles from 'Views/backend/Articles.vue';
import Roles from 'Views/backend/roles.vue';
import Settings from 'Views/backend/settings.vue';

import Login from 'Views/backend/login/login.vue'

const router = new Router({
    mode: 'history',
    routes: [
        {
            path: '/backend',
            component: Main,
            redirect: '/backend/dashboard',
            children: [
                {
                    path: '/backend/dashboard',
                    meta: {
                        requiresAuth: true,
                        type: 'admin',
                        permissions: []
                    }


                },
                {
                    path: '/backend/articles',
                    component: Articles,
                    meta: {
                        requiresAuth: true,
                        type: 'admin',
                        permissions: ['view-his-articles']
                    }
                },
                {
                    path: '/backend/admins',
                    component: Admins,
                    meta: {
                        requiresAuth: true,
                        type: 'admin',
                        permissions: ['view-admins']
                    }
                },
                {
                    path: '/backend/roles',
                    component: Roles,
                    meta: {
                        requiresAuth: true,
                        type: 'admin',
                        permissions: ['view-roles']
                    }
                },
                {
                    path: '/backend/setting',
                    component: Settings,
                    meta: {
                        requiresAuth: true,
                        type: 'admin',
                        permissions: ['edit-his-profile-data', 'edit-his-password']
                    }
                }

            ],
        },
        {
            path: '/backend/login',
            component: Login
        }
    ],
});
router.beforeEach((to, from, next) => {

    if (to.matched.some(record => record.meta.requiresAuth)) {
        return store.dispatch('getUserInformation', to.meta.type)
            .then(response => {
                let canView = to.meta.permissions.length && to.meta.permissions.some(permission => {
                    return response.all_permissions.includes(permission)
                });
                next();
                if (!canView) {

                    next({
                        path: '/backend/dashboard',
                    });
                    return false;
                }
                console.log('entering');
                next();
            }).catch(error => {
                console.log(error)
                next({
                    path: '/backend/login',
                });
            })
    }
    next();

})

export default router;