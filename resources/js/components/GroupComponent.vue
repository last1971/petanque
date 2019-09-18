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
                    <b-btn :to="{ name: 'event', params: { id: event.id } }">
                        <i class="fas fa-hand-point-left" aria-hidden="true"></i>
                    </b-btn>
                    <b-btn :disabled="!new_roond_possible" @click="make_round">
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
                                Ран.
                            </th>
                            <th scope="col">
                                Бух.
                            </th>
                            <th scope="col">
                                Очк.
                            </th>
                        </thead>
                        <tbody>
                            <tr v-for="(value, index) in teams" :key="index">
                                <td>
                                    <b-btn class="col-1" v-if="!end_team_enter" variant="danger" size="sm" @click="remove_team(value.id)">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </b-btn>
                                    <span v-else>
                                        {{ index + 1}}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ value.name }}</div>
                                    <div v-if="value.was_names">
                                        <span v-for="(w, i) in value.was_names" :key="i" class="small">
                                            {{ w }}, {{ ' ' }}
                                        </span>
                                    </div>
                                </td>
                                <td> {{ value.winner }}</td>
                                <td> {{ value.rank }}</td>
                                <td> {{ value.buhgolc }}</td>
                                <td> {{ value.points }}</td>
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
                    </b-input-group>
                </div>
            </b-card>
        </b-card-group>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    export default {
        name: "GroupComponent",

        data() {
            return {
                group_id: 0,
                event: { id: 1, rounds: 0 },
                items: [
                    {
                        text: 'События',
                        to: { name: 'home' }
                    },
                ],
                team_name: '',
                end_team_enter: false,
                stop_flag: false
            }
        },

        computed: {
            ...mapGetters({
                teams: 'TEAM/DATA',
                rounds: 'ROUND/DATA'
            }),
            new_roond_possible() {
                return this.end_team_enter && this.rounds.length < this.event.rounds
            },
            stop_commands() {
                return !this.event.tracks ? true : this.rounds.length > 0 || this.teams.length != this.event.tracks.length * 2
            },
            input_teams() {
                return this.event.tracks ?  !this.end_team_enter && this.event.tracks.length * 2 > this.teams.length : false
            }
        },

        watch: {
            end_team_enter(val) {
                if (this.rounds.length == 0 && val) {
                    this.$store.dispatch('TEAM/SET_QUERY', { group_id: this.group_id, renumber: true, per_page: 100 })
                        .then(res => {} )
                }
            }
        },

        methods: {
            update_group(to) {
                this.group_id = to.params.id
                this.$store.dispatch('TEAM/SET_QUERY', { group_id: this.group_id, per_page: 100 })
                    .then(res => {
                        if (this.event.tracks) {
                            this.end_team_enter = this.teams.length == this.event.tracks.length * 2
                        }
                    })
                this.$store.dispatch('ROUND/SET_QUERY', { group_id: this.group_id, per_page: 100 })
                this.$store.dispatch('GROUP/CHECK_CACHE', this.group_id)
                    .then(res => {
                        this.event = res.event
                        this.end_team_enter = this.teams.length == res.event.tracks.length * 2
                        this.items = [
                            {
                                text: 'События',
                                to: { name: 'home' }
                            },
                            {
                                text: 'Текущее "' + res.event.name + '"',
                                to: { name: 'event', params: { id: res.event.id } }
                            },
                            {
                                text: res.name,
                                active: true
                            },
                        ]
                    })
            },
            add_team() {
                this.$store.dispatch('TEAM/TO_PAGE', { name: this.team_name, group_id: this.group_id })
                    .then(res => {
                        this.team_name = ''
                    })
            },
            remove_team(id) {
                this.$store.dispatch('TEAM/DELETE_WITH', { id: id, with: this.group_id })
            },
            make_round() {
                this.$store.dispatch('ROUND/TO_PAGE', { id: 0, group_id: this.group_id })
                    .then(res => {
                        this.$store.commit('ROUND/SORT_DATA', ['number'])
                    })
            },
            remove_round() {
                let id  = _.last(this.rounds).id;
                this.$store.dispatch('ROUND/DELETE', id);
            }
        },

        beforeRouteEnter(to, from, next) {
            next(vm => { vm.update_group(to) })
        },

        beforeRouteUpdate(to, from, next) {
            this.update_group(to)
            next()
        }
    }
</script>

<style scoped>

</style>
