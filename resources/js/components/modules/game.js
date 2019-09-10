import model from './models'
import _ from 'lodash'

let state = _.cloneDeep(model.state)
let getters = model.getters
let mutations = model.mutations
let actions = model.actions

state.url = '/api/game'
state.cache[0] = {
    id: 0,
    name: '',
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
}
