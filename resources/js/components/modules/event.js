import model from './models'
import _ from 'lodash'

let state = _.cloneDeep(model.state)
let getters = model.getters
let mutations = model.mutations
let actions = model.actions

const date = new Date()
state.url = '/api/event'
state.cache[0] = {
    id: 0,
    name: '',
    date:  date.getDate().toString() + '/' + (date.getMonth()+1).toString() + '/' + date.getFullYear().toString(),
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
}
