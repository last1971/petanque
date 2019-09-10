import Vue  from 'vue';
import Vuex from 'vuex';

import auth from './modules/auth';
import event from './modules/event';
import group from './modules/group'
import track from './modules/track'
import team from "./modules/team";
import round from './modules/round'
import game from './modules/game'
import member from './modules/member'

Vue.use(Vuex);

export default new Vuex.Store({
    state: {},
    mutations: {},
    actions: {},
    getters : {},
    modules:{
        auth,
        EVENT: event,
        GROUP: group,
        TRACK: track,
        TEAM: team,
        ROUND: round,
        GAME: game,
        MEMBER: member
    }
})
