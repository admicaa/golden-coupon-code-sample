import axios from 'axios';
const state = {
    permissions: []
};


const getters = {
    permissions(state) {
        return state.permissions;
    }
};


const actions = {
    getRoles(context, options) {
        return axios.get('/api/roles', {
            params: options
        })
    },
    getPermissions(context) {
        return axios.get('/api/roles/permissions')
            .then(response => {
                context.commit('setPermissions', response.data);
            })
    },
    addNewRole(context, role) {
        return axios.post('/api/roles/create', role);
    },
    editRole(context, role) {
        return axios.put(`/api/roles/edit/${role.id}`, role);
    },
    deleterole(context, role) {
        return axios.delete(`/api/roles/delete/${role.id}`);
    }
};


const mutations = {
    setPermissions(state, data) {
        state.permissions = data;
    }
};

export default {
    state,
    getters,
    actions,
    mutations
}