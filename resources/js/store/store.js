import Vue from 'vue'
import Vuex from 'vuex'

// modules
import auth from './modules/auth';
import admins from './modules/admins/index'
import roles from './modules/rules/index'
import articles from './modules/articles/index'
Vue.use(Vuex);

export const store = new Vuex.Store({
    modules: {
        auth,
        admins,
        roles,
        articles
    }
})
