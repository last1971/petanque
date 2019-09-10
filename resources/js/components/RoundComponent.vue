<template>
    <div class="mt-4">
        <b-breadcrumb :items="items"></b-breadcrumb>
        <div class="row mb-1" v-for="(value, index) in games" :key="index">
            <div class="col-1">
                <b-badge variant="light">{{ value.track.name }}</b-badge>
            </div>
            <b-input-group class="col-5">
                <b-input-group-prepend is-text>
                    {{ value.members[0].team.name }}
                </b-input-group-prepend>
                <b-form-input v-model="value.members[0].points"
                              type="number" min="0" max="13"
                              :disabled="mode(value)"
                              :class="color(value, 0)"
                >
                </b-form-input>
            </b-input-group>
            <b-input-group class="col-5">
                <b-form-input v-model="value.members[1].points"
                              type="number" min="0" max="13"
                              :disabled="mode(value)"
                              :class="color(value, 1)"
                ></b-form-input>
                <b-input-group-append is-text>
                    {{ value.members[1].team.name }}
                </b-input-group-append>
            </b-input-group>
            <div class="col-1 justify-content-center d-flex">
                <b-btn v-if="mode(value)" @click="edit(value)" aria-hidden="true">
                    <i class="fas fa-edit"></i>
                </b-btn>
                <b-btn v-else @click="save(value)" aria-hidden="true">
                    <i class="fas fa-save"></i>
                </b-btn>
            </div>
        </div>
        <b-btn @click="reload">
            <i class="fas fa-hand-point-left" aria-hidden="true"></i> Вернуться
        </b-btn>
        <b-btn @click="random">
            <i class="fas fa-random" aria-hidden="true"></i> От фонаря
        </b-btn>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    export default {
        name: "RoundComponent",

        computed: {
            ...mapGetters({
                games: 'GAME/DATA'
            })
        },

        data() {
            return {
                round_id: 0,
                items: [
                    {
                        text: 'События',
                        to: { name: 'home' }
                    },
                ],
            }
        },
        methods:{
            getRandomInt(max) {
                return Math.floor(Math.random() * Math.floor(max));
            },

            random() {
                this.games.forEach(value => {
                    value.members[0].points = this.getRandomInt(14)
                    let flag = true;
                    while (value.members[0].points == value.members[1].points || flag) {
                        flag = false
                        value.members[1].points = this.getRandomInt(14)
                    }
                    this.save(value)
                });
            },

            reload() {
                this.$store.dispatch('TEAM/LOAD')
                    .then(res => this.$router.go(-1))
            },
            save(value) {
                if (parseInt(value.members[0].points) > parseInt(value.members[1].points)) {
                    value.members[0].winner = true
                    value.members[1].winner = false
                } else if (parseInt(value.members[0].points) < parseInt(value.members[1].points)) {
                    value.members[0].winner = false
                    value.members[1].winner = true
                } else {
                    value.members[0].winner = null
                    value.members[1].winner = null
                }
                this.$store.dispatch('MEMBER/TO_PAGE', value.members[0])
                this.$store.dispatch('MEMBER/TO_PAGE', value.members[1])
                this.$set(value, 'edit', false)
            },

            edit(value) {
                this.$set(value, 'edit', true)
            },

            color(game, index) {
                return !this.mode(game) ? '' : (game.members[index].winner ? 'bg-success' : 'bg-danger')
            },

            mode(game) {
                return !(game.edit || game.members[0].winner === null || game.members[0].winner === null)
            },

            update_round(to) {
                this.round_id = to.params.id
                this.$store.dispatch('GAME/SET_QUERY', { round_id: this.round_id, per_page: 100 })
                this.$store.dispatch('ROUND/CHECK_CACHE', this.round_id)
                    .then(result => {
                        this.$store.dispatch('GROUP/CHECK_CACHE', result.group_id)
                            .then(res => {
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
                                        to: { name: 'group', params: { id: result.group_id } }
                                    },
                                    {
                                        text: 'Раунд ' +  result.number,
                                        active: true
                                    }
                                ]
                            })
                    })
            }
        },

        beforeRouteEnter(to, from, next) {
            next(vm => { vm.update_round(to) })
        },

        beforeRouteUpdate(to, from, next) {
            this.update_round(to)
            next()
        }
    }
</script>

<style scoped>

</style>
