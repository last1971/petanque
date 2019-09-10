import axios from 'axios'
import page_info from './page-info'
import _ from 'lodash'
import Swal from 'sweetalert2';

let state = {
    /**
     * API url
     */
    url: '',
    /**
     * Page info
     */
    page_info: _.cloneDeep(page_info),
    /**
     *  Page change
     */
    page_change: false,
    /**
     * Page data
     */
    data: [],
    /**
     * Query object
     */
    query: {},
    /**
     *  Additional query parameters
     */
    additional_query_parameters: {},
    /**
     * Cache data
     */
    cache: [],
    /**
     * Last change id
     */
    last_change_id: null,
    /**
     * Array requests to api
     */
    requests: []
}

let getters = {
    URL: state => state.url,
    PAGE_INFO: state => state.page_info,
    PAGE_CHANGE: state => state.page_change,
    DATA: state => state.data,
    QUERY: state => _.cloneDeep(state.query),
    ADDITIONAL_QUERY_PARAMETERS: state => state.additional_query_parameters,
    FULL_QUERY: state => {
        return {...state.query, ...state.additional_query_parameters}
    },
    CACHE: state => (id, multiply = false) => {
        let ids = _.isArray(id) ? id : id.toString().split(',')
        ids = ids.map(value => parseInt(value))
        return multiply ? _.filter(state.cache, value => ids.indexOf(parseInt(value.id)) >= 0)
            : _.find(state.cache, { id: parseInt(id) })
    },
    CACHE_QUERY: state => payload => _.filter(state.cache, payload),
    ALL_CACHE: state => state.cache,
    ON_PAGE: state => id => id == 0 ? state.cache[id] :  _.find(state.data, { id: parseInt(id) }),
    LAST_CHANGE: state => _.find(state.cache, { id: parseInt(state.last_change_id) }),
    GET_REQUEST: state => query => _.find(state.requests, { query: query })
}

let mutations = {
    SET_URL(state, url){
        state.url = url
    },

    SET_PAGE(state, page) {
        if (_.isObject(page)) {
            //Копируем значения только для существующих в page_info ключей
            _.forOwn(page, (value, key) => {
                if (state.page_info[key] !== undefined) {
                    state.page_info[key] = value
                }
            })
            state.data = page.data
        }
    },

    PAGE_CHANGE(state, page) {
        state.page_change = page
    },

    SET_QUERY(state, query) {
        state.query = _.cloneDeep(query)
    },

    SET_QUERY_PARAMETER(state, payload ) {
        _.each(payload, (value, key) => state.query[key] = value)
    },

    SET_ADDITIONAL_QUERY_PARAMETERS(state, payload ) {
        _.each(payload, (value, key) => state.additional_query_parameters[key] = value)
    },

    ADD_CACHE(state, value) {
        const newValues = _.isArray(value) ? value : [ value ]
        newValues.forEach((val) => {
            let index = _.findIndex(state.cache, { 'id' : parseInt(val.id) })
            if (index >= 0) {
                Vue.set(state.cache, index, val)
            } else {
                state.cache.push(val)
            }
            index = _.findIndex(state.data, { 'id' : parseInt(val.id) })
            if (index >= 0) {
                Vue.set(state.data, index, val)
            }
        })
        state.last_change_id = _.isArray(value) ?
            (value.length > 0 ? value[value.length - 1].id : state.last_change_id) :
            value.id
    },

    DEL_CACHE(state, id) {
        _.remove(state.cache, { id: id })
    },

    DEL_DATA(state, id) {
        const index = _.findIndex(state.data, {id: id})
        if (index >= 0) {
            state.data.splice(index,1)
        }
    },

    TO_PAGE(state, value) {
        state.data = _.unionBy(_.isArray(value) ? value : [ value ], state.data, 'id')
    },

    ADD_REQUEST(state, payload) {
        state.requests.push(payload)
    },

    DEL_REQUEST(state, query) {
        _.remove(state.requests, { query: query })
    },

    SORT_DATA(state, payload) {
        state.data = _.sortBy(state.data, payload);
    }

}
/**
 * payload.query - параметры запроса, если не указаны то будут взяты из состояния
 * payload.cache - кэшировать или нет результаты
 */
let actions = {
    LOAD({state, commit, getters}, payload ){
        const query = payload && payload.query ? payload.query : getters.FULL_QUERY
        if (query.page) {
            query.page = parseInt(query.page)
        }
        let request = getters.GET_REQUEST(query)
        if (request) {
            request = request.request
        } else {
            request = new Promise((resolve, reject) => {
                axios.get(state.url, {params: query})
                    .then(response => {
                        if (!(payload && payload.query)) {

                            commit('SET_PAGE', response.data)
                        }
                        if (payload && payload.cache) {
                            if (response.data.data) {
                                commit('ADD_CACHE', response.data.data)
                            } else if (response.data.id) {
                                commit('ADD_CACHE', response.data)
                            }
                        }
                        resolve(response)
                    })
                    .catch((error) => reject(error))
                    .then(_ => {
                        commit('DEL_REQUEST', query)
                    })
            })
            commit('ADD_REQUEST', { query: query, request: request })
        }
        return request
    },

    SET_QUERY({state, dispatch, commit}, query) {
        return new Promise((resolve, reject) => {
            if (_.isEqual(state.query, query)) {
                resolve(true)
            } else {
                commit('SET_QUERY', query)
                dispatch('LOAD', { cache: query.cache })
                    .then(result => {
                        resolve(result)
                    })
                    .catch(error => {
                        reject(error)
                    })
            }
        })
    },

    SET_QUERY_PARAMETER({state, dispatch, commit}, payload) {
        return new Promise((resolve, reject) => {
            commit('SET_QUERY_PARAMETER', payload)
            dispatch('LOAD')
                .then(result => {
                    resolve(result)
                })
                .catch(error => {
                    reject(error)
                })
        })
    },

    DELETE_WITH({ commit, state }, payload) {
        return new Promise(( resolve, reject ) => {
            axios.delete(state.url + '/' + payload.with + ',' + payload.id)
                .then(() => {
                    commit('DEL_CACHE', payload.id)
                    commit('DEL_DATA', payload.id)
                    resolve(true)
                })
                .catch(error => {
                    reject(error)
                    Swal.fire({
                        title: 'Ошибка',
                        text: error.response.data.message,
                        type: 'error',
                        timer: 10000
                    });
                });
        })

    },

    DELETE_LAST({ commit, state }, id) {
        return new Promise(( resolve, reject ) => {
            axios.delete(state.url + '/' + id)
                .then(() => {
                    const del_id = _.last(state.data).id
                    commit('DEL_CACHE', del_id)
                    commit('DEL_DATA', del_id)
                    resolve(true)
                })
                .catch(error => {
                    reject(error)
                    Swal.fire({
                        title: 'Ошибка',
                        text: error.response.data.message,
                        type: 'error',
                        timer: 10000
                    });
                });
        })
    },

    DELETE({ commit, state }, id) {
        return new Promise(( resolve, reject ) => {
            axios.delete(state.url + '/' + id)
                .then(_ => {
                    commit('DEL_CACHE', id)
                    commit('DEL_DATA', id)
                    resolve(true)
                })
                .catch(error => {
                    reject(error)
                    Swal.fire({
                        title: 'Ошибка',
                        text: error.response.data.message,
                        type: 'error',
                        timer: 5000
                    });
                });
        })

    },

    CHECK_CACHE({state, commit, getters}, id) {
        let request = getters.GET_REQUEST(id)
        if (request) {
            request = request.request
        } else {
            request = new Promise((resolve, reject) => {
                if (getters.CACHE(id)) {
                    return resolve(getters.CACHE(id))
                }
                axios.get(state.url + '/' + id)
                    .then(response => {
                        commit('ADD_CACHE', response.data)
                        resolve(response.data)
                    })
                    .catch(error => {
                        reject(error)
                    })
                    .then(_ => {
                        commit('DEL_REQUEST', id)
                    })
            })
            commit('ADD_REQUEST', { query: id, request: request})
        }
        return request
    },

    TO_PAGE({state, commit, getters, dispatch}, value) {
        return new Promise((resolve, reject) => {
            let method = 'post'
            let url = state.url
            if (value.id > 0) {
                url = url + '/' + value.id
                method = 'put'
            }
            let saveVal = _.cloneDeep(value)
            saveVal.page_query = getters.FULL_QUERY
            axios[method](url, saveVal)
                .then(response => {
                    commit('ADD_CACHE', response.data)
                    if (response.data.page && response.data.page != state.query.page) {
                        commit('PAGE_CHANGE', response.data.page)
                    } else {
                        commit('TO_PAGE', response.data)
                    }
                    resolve(response.data)
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Не предвиденная ошибка',
                        text:  error.response.data.message,
                        type:  'error',
                        timer: 5000
                    });
                    reject(error)
                })
        })
    }
}

export default {
    state,
    getters,
    mutations,
    actions
}
