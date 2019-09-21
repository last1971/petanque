<template>
    <div class="mt-4">
        <b-breadcrumb  :items="items"></b-breadcrumb>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">
                        Дата
                    </th>
                    <th scope="col">
                        Наименование
                    </th>
                    <th scope="col">
                        Раундов
                    </th>
                    <th scope="col">
                        Создал
                    </th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(value, index) in data" :key="index">
                    <td>{{ value.date }}</td>
                    <td>
                        <router-link :to="{ name: 'event', params: { id: value.id } }">{{ value.name }}</router-link>
                    </td>
                    <td>{{ value.rounds.length }}</td>
                    <td>{{ value.user.name }}</td>
                    <td>
                        <b-btn variant="danger" @click="remove_event(value.id)">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                        </b-btn>
                    </td>
                </tr>
                <tr v-if="insert_mode">
                    <td><b-input v-model="new_event.date"></b-input></td>
                    <td><b-input v-model="new_event.name"></b-input></td>
                    <td>0</td>
                    <td>{{ user_name }}</td>
                    <td>
                        <b-btn variant="success" @click="add_event">
                            <i class="fas fa-hand-point-up" aria-hidden="true"></i>
                        </b-btn>
                    </td>
                </tr>
            </tbody>

        </table>
        <paginate-component
            :objects="page"
            :path="{ path: $route.path , query: { } }"
            class="mt-2"
            @insert="insert"
        ></paginate-component>
        <b-btn @click="logout" variant="warning">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            Выход
        </b-btn>

    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import PaginateComponent from "./PaginateComponent";
    import Datepicker from "vue-bootstrap-datetimepicker";
    export default {
        name: "HomeComponent",
        components: { PaginateComponent, Datepicker },
        computed: {
            ...mapGetters({ data: 'EVENT/DATA', page: 'EVENT/PAGE_INFO', user: 'USER' }),
            user_name() {
                return this.user ? this.user.name : ''
            }
        },
        data() {
            return {
                insert_mode: false,
                new_event: {
                    id: 0,
                    name: '',
                    date:  (new Date()).getDate().toString() + '/' + ((new Date()).getMonth()+1).toString() + '/' + (new Date()).getFullYear().toString()
                },
                items: [
                    {
                        text: 'События',
                        active: true
                    },
                ],
            }
        },
        methods: {
            logout() {
                this.$store.dispatch('LOGOUT').then(res => { this.$router.push({ name: 'login' })})
            },

            insert() {
                this.insert_mode = !this.insert_mode
            },
            add_event() {
                this.new_event.user_id = this.user.id
                this.$store.dispatch('EVENT/TO_PAGE', this.new_event)
                    .then(res => {
                        this.new_event = _.cloneDeep(this.$store.getters['EVENT/CACHE'](0))
                        this.insert_mode = false
                    })
            },
            remove_event(id) {
                this.$store.dispatch('EVENT/DELETE', id)
            }
        },
        created() {
            this.$store.dispatch('EVENT/SET_QUERY', { page: this.$route.query.page ? this.$route.query.page : 1 })
        },
        beforeRouteUpdate(to, from, next) {
            if (to.params.page) {
                this.$store.dispatch('EVENT/SET_QUERY', { page: to.query.page })
            }
            next()
        }
    }
</script>

<style scoped>

</style>
