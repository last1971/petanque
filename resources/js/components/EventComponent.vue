<template>
    <div class="mt-4">
        <b-breadcrumb :items="items"></b-breadcrumb>
        <b-card-group deck>
            <b-card header="Группы">
                <b-card-body>
                    <router-link v-for="(value, index) in data"
                                 :key="index"
                                 :to="{ name: 'group', params: { id: value.id }}"
                    >
                        {{ value.name }}
                    </router-link>
                </b-card-body>
                <div slot="footer">
                    <b-btn :to="{ name: 'home' }">
                        <i class="fas fa-hand-point-left" aria-hidden="true"></i>
                    </b-btn>
                    <b-btn v-if="tracks.length > 3" variant="success" @click="add_group" :disabled="event_started">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                    </b-btn>
                    <b-btn v-if="tracks.length > 3 && data.length > 0" variant="danger" @click="remove_group" :disabled="event_started">
                        <i class="fas fa-minus" aria-hidden="true"></i>
                    </b-btn>
                </div>
            </b-card>
            <b-card header="Дорожки">
                <b-card-body>
                    <span v-for="(value, index) in tracks">
                        <b>{{ value.name + ', ' }}</b>
                    </span>
                </b-card-body>
                <div slot="footer">
                    <b-btn :to="{ name: 'home' }">
                        <i class="fas fa-hand-point-left" aria-hidden="true"></i>
                    </b-btn>
                    <b-btn variant="success" @click="add_track" :disabled="event_started">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                    </b-btn>
                    <b-btn v-if="tracks.length > 4"variant="danger" @click="remove_track" :disabled="event_started">
                        <i class="fas fa-minus" aria-hidden="true"></i>
                    </b-btn>
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
                event_id: 0,
                event_started: false,
                items: [
                    {
                        text: 'События',
                        to: { name: 'home' }
                    },
                    {
                        text: 'Текущее',
                        active: true
                    }
                ]
            }
        },

        computed: {
            ...mapGetters({ data: 'GROUP/DATA', tracks: 'TRACK/DATA' }),
        },

        methods: {
            update_event(to) {
                this.event_id = to.params.id
                this.$store.dispatch('GROUP/SET_QUERY', { event_id: to.params.id, per_page: 30 })
                this.$store.dispatch('TRACK/SET_QUERY', { event_id: to.params.id, per_page: 40 })
                this.$store.dispatch('EVENT/CHECK_CACHE', this.event_id)
                    .then(res => {
                        this.items[1].text = 'Текущее "' + res.name + '"'
                        this.event_started = res.groups.reduce( (f, group) => {
                            return f || group.rounds.length > 0
                        }, false)
                    })
            },
            add_group() {
                this.$store.dispatch('GROUP/TO_PAGE', { event_id: this.event_id })
                    .then(() => this.$store.commit('GROUP/SORT_DATA', ['name']))
            },
            remove_group() {
                this.$store.dispatch('GROUP/DELETE', this.data[this.data.length - 1].id)
            },
            add_track() {
                this.$store.dispatch('TRACK/TO_PAGE', { event_id: this.event_id })
                    .then(() => this.$store.commit('TRACK/SORT_DATA', ['name']))
            },
            remove_track() {
                this.$store.dispatch('TRACK/DELETE_LAST', this.event_id)
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
