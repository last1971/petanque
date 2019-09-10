import axios from 'axios';

let state = {
    status: '',
    token: localStorage.getItem('token') || document.head.querySelector('meta[name="token"]'),
    user : {},

};

let getters = {
    IS_LOGGEDIN: state => !!state.token,
    AUTH_STATUS: state => state.status,
    USER: state => state.user,
};

let mutations = {
    AUTH_REQUEST(state){
        state.status = 'loading';
    },

    AUTH_SUCCESS(state, token){
        state.status = 'success';
        state.token = token;
    },

    AUTH_ERROR(state){
        state.status = 'error';
    },

    LOGOUT(state){
        state.status = '';
        state.token = '';
    },

    SET_USER(state, user) {
        state.user = user;
    },

    SET_TOKEN(state, token) {
        state.token = token
    },

};

let actions = {
    LOGIN({commit}, user){
        return new Promise((resolve, reject) => {
            commit('AUTH_REQUEST');
            axios({url: '/api/login', data: user, method: 'POST' })
                .then(resp => {
                    const token = resp.data.token;
                    const user = resp.data.user;
                    localStorage.setItem('token', token);
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
                    commit('AUTH_SUCCESS', token);
                    commit('SET_USER', user)
                    resolve(resp);
                })
                .catch(err => {
                    commit('AUTH_ERROR');
                    localStorage.removeItem('token');
                    reject(err);
                })
        })
    },

    LOGOUT({commit}){
        return new Promise((resolve, reject) => {
            axios.get('/api/logout');
            commit('LOGOUT');
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
            resolve()
        })
    },

    SET_USER({commit}) {
        return axios.get('/api/user')
            .then(response => {
                commit('SET_USER', response.data)
            })
    },
};

export default {
    state,
    getters,
    mutations,
    actions
};
