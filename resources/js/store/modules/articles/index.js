import axios from 'axios';

const normalizeArticle = article => {
    const page = article.page || {};

    return Object.assign({}, article, {
        name: page.name || article.name || '',
        title: page.title || article.title || '',
        slug: page.slug || article.slug || '',
        body: page.description || article.body || '',
        description: page.description || article.description || '',
    });
};

const state = {

};


const getters = {

};


const actions = {
    getArticles(context, options) {
        return axios.get('/api/articles', {
            params: Object.assign({
                body: 1
            }, options)
        }).then(response => {
            response.data.data = response.data.data.map(normalizeArticle);

            return response;
        });
    },
    getArticle(context, articleId) {
        return axios.get(`/api/articles/${articleId}`, {
            params: {
                body: 1
            }
        }).then(response => {
            response.data = normalizeArticle(response.data);

            return response;
        });
    },
    addArticle(context, article) {
        return axios.post('/api/articles', article).then(response => {
            response.data = normalizeArticle(response.data);

            return response;
        });
    },
    updateArticle(context, article) {
        return axios.put(`/api/articles/${article.id}`, article).then(response => {
            response.data = normalizeArticle(response.data);

            return response;
        });
    },
    deleteArticle(context, article) {
        return axios.delete(`/api/articles/${article.id}`);
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
