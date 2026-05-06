
import Cookie from 'js-cookie';
import router from '@/router/index.js'
let token = Cookie.get('token');
import axios from 'axios';

const state = {
    user: null,
    token: token
};


const getters = {
    user(state) {
        return state.user;
    },
    token(state) {
        return state.token
    }
};


const actions = {
    login(context, { user, type }) {
        axios.post(`/api/login/${type}`, user)
            .then(response => {
                context.commit('loginUserSuccess', response.data);
            });
    },
    getUserInformation(context, type) {
        if (context.getters.user && context.getters.token) {
            if (context.getters.user.type === type) {
                return context.getters.user;
            }
        }
        if (context.getters.token) {
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + context.getters.token;
            return axios.get('/api/' + type + '/user')
                .then(response => {
                    context.commit('setUser', response.data);
                    return response.data;
                })
        }
        return new Promise((resolve, reject) => {
            reject('401 Unauthorized');
        })
    }
};


const mutations = {
    loginUserSuccess(state, response) {
        const token = response['token'];
        state.user = response['user'];
        Cookie.set('token', token);
        state.token = token;
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + state.token;

        router.push("/backend/dashboard");
        // setTimeout(function () {
        //     Vue.notify({
        //         group: 'loggedIn',
        //         type: 'success',
        //         text: 'User Logged In Success!'
        //     });
        // }, 1500);
    },
    setUser(state, user) {
        state.user = user;
    }
};

export default {
    state,
    getters,
    actions,
    mutations
}
