import model from './models'
import _ from 'lodash'

let state = _.cloneDeep(model.state)
let getters = model.getters
let mutations = model.mutations
let actions = model.actions

state.url = '/api/group'
state.cache[0] = {
    id: 0,
    name: '',
    event_id: 0
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
}
