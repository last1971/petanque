import model from './models'
import _ from 'lodash'

let state = _.cloneDeep(model.state)
let getters = model.getters
let mutations = model.mutations
let actions = model.actions

state.url = '/api/team'
state.cache[0] = {
    id: 0,
    name: '',
    group_id: 0
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
}
