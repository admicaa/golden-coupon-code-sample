import axios from 'axios';
const state = {

};


const getters = {

};


const actions = {
    getArticles(context, options) {
        return axios.get('/api/articles', {
            params: options
        });
    },
    addArticle(context, article) {
        return axios.post('/api/articles/create', article)

    },
    updateArticle(context, article) {
        return axios.put(`/api/articles/update/${article.id}`, article);
    },
    deleteArticle(context, article) {
        return axios.delete(`/api/articles/delete/${article.id}`);
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