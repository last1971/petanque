<template>
    <div class="mt-4">
        <b-breadcrumb :items="items"></b-breadcrumb>
        <b-card-group deck>
            <b-card header="Раунды" style="max-width: 20rem;">
                <b-card-body>
                    <div v-for="(value, index) in rounds" :key="index">
                        <router-link :to="{ name: 'round', params: { id: value.id } }">
                            {{ value.number + ' ' }} раунд
                        </router-link>
                    </div>
                </b-card-body>
                <div slot="footer">
                    <b-btn :to="{ name: 'home' }">
                        <i class="fas fa-hand-point-left" aria-hidden="true"></i>
                    </b-btn>
                    <b-btn :disabled="!new_round_possible" @click="make_round">
                        Новый раунд
                    </b-btn>
                    <b-btn v-if="rounds.length > 0"variant="danger" @click="remove_round">
                        <i class="fas fa-minus" aria-hidden="true"></i>
                    </b-btn>
                </div>
            </b-card>
            <b-card header="Команды">
                <b-card-body>
                    <table class="table">
                        <thead>
                            <th scope="col">
                                №
                            </th>
                            <th scope="col">
                                Наименование
                            </th>
                            <th scope="col">
                                Поб.
                            </th>
                            <th scope="col">
                                Бух.
                            </th>
                            <th scope="col">
                                Разн.
                            </th>
                            <th scope="col">
                                М.Бух.
                            </th>
                            <th scope="col">
                                Ран.
                            </th>
                        </thead>
                        <tbody>
                            <tr v-for="(value, index) in teams" :key="index">
                                <td>
                                    <span>
                                        {{ index + 1}}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ value.name }}</div>
                                    <div v-if="value.was_names && soperniki">
                                        <span v-for="(w, i) in value.was_names" :key="i" class="small">
                                            {{ w }}, {{ ' ' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <b-btn v-if="!end_team_enter" variant="danger" size="sm" @click="remove_team(value.id)">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </b-btn>
                                    <span v-else>
                                        {{ value.winner }}
                                    </span>
                                </td>
                                <td> {{ value.buhgolc }}</td>
                                <td> {{ value.points }}</td>
                                <td> {{ value.mega_buhgolc }}</td>
                                <td> {{ value.rank }}</td>
                            </tr>
                        </tbody>
                    </table>
                </b-card-body>
                <div slot="footer">
                    <b-input-group>
                        <b-input-group-prepend is-text>
                            <b-checkbox v-model="end_team_enter" class="pr-2" :disabled="stop_commands">Команды набраны</b-checkbox>
                        </b-input-group-prepend>
                        <b-input v-model="team_name" @keypress.enter="add_team" v-if="input_teams"></b-input>
                        <b-input-group-append v-if="input_teams">
                            <b-btn @click="add_team"><i class="fas fa-hand-point-up" aria-hidden="true"></i></b-btn>
                        </b-input-group-append>
                        <b-input-group-prepend v-else is-text>
                            <b-checkbox v-model="soperniki" class="pr-2">Соперники</b-checkbox>
                        </b-input-group-prepend>
                    </b-input-group>
                </div>
            </b-card>
        </b-card-group>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    export default {
        name: "EventComponent",

        data() {
            return {
                event: { id: 0, rounds: [], name: '' },
                items: [
                    {
                        text: 'События',
                        to: { name: 'home' }
                    },
                ],
                team_name: '',
                end_team_enter: false,
                stop_flag: false,
                soperniki: false,
            }
        },

        computed: {
            ...mapGetters({
                teams: 'TEAM/DATA',
                rounds: 'ROUND/DATA'
            }),
            new_round_possible() {
                return this.end_team_enter
            },
            stop_commands() {
                return this.teams.length < 10 || this.event.rounds.length > 0
            },
            input_teams() {
                return !this.end_team_enter
            }
        },

        watch: {
            end_team_enter(val) {
                if (this.rounds.length == 0 && val) {
                    this.$store.dispatch('TEAM/SET_QUERY', { event_id: this.event.id, per_page: 100 })
                        .then(res => {} )
                }
            }
        },

        methods: {
            update_event(to) {
                this.$store.dispatch('EVENT/CHECK_CACHE', to.params.id)
                    .then(res => {
                        this.event = res
                        this.end_team_enter = this.event.rounds.length > 0
                        this.items = [
                            {
                                text: 'События',
                                to: { name: 'home' }
                            },
                            {
                                text: 'Текущее "' + res.name + '"',
                                active: true
                            },
                        ]
                        this.$store.dispatch('TEAM/SET_QUERY', { event_id: this.event.id, per_page: 100 })
                        this.$store.dispatch('ROUND/SET_QUERY', { event_id: this.event.id, per_page: 100 })
                    })
            },

            add_team() {
                this.$store.dispatch('TEAM/TO_PAGE', { name: this.team_name, event_id: this.event.id })
                    .then(res => {
                        this.team_name = ''
                    })
            },

            remove_team(id) {
                this.$store.dispatch('TEAM/DELETE_WITH', { id: id, with: this.event.id })
            },

            make_round() {
                this.$store.dispatch('ROUND/TO_PAGE', { id: 0, event_id: this.event.id })
                    .then(res => {
                        this.$store.commit('ROUND/SORT_DATA', ['number'])
                        this.event.rounds.push(res)
                    })
            },

            remove_round() {
                let id  = _.last(this.rounds).id;
                this.$store.dispatch('ROUND/DELETE', id)
                    .then(res => {
                        this.$store.dispatch('TEAM/LOAD')
                    })
            }
        },

        beforeRouteEnter(to, from, next) {
            next(vm => { vm.update_event(to) })
        },

        beforeRouteUpdate(to, from, next) {
            this.update_event(to)
            next()
        }
    }
</script>

<style scoped>

</style>
