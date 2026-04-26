import axios from 'axios';
const state = {

};


const getters = {

};


const actions = {
    getAdminUsers(context, options) {

        return axios.get('/api/admins', {
            params: options
        })

    },
    addNewAdmin(context, admin) {
        return axios.post('/api/admins/create', admin);
    },
    deleteAdmin(context, admin) {
        return axios.delete(`/api/admins/delete/${admin.id}`)
    },
    updateAdmin(context, admin) {
        let id = admin.id ? admin.id : admin.get('id');
        return axios.post(`/api/admins/update/${id}`, admin);
    }
};


const mutations = {

};

export default {
    state,
    getters,
    actions,
    mutations
}