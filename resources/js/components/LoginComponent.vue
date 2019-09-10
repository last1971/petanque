<template>
    <div class="mt-4">
        <p class="h4 text-center mb-4">Sign in</p>
        <b-form class="grey-text">
            <b-input-group class=mb-4>
                <b-input-group-prepend is-text><i class="fa fa-envelope" aria-hidden="true"></i></b-input-group-prepend>
                <b-input placeholder="Your email"  type="email" :state="!invalid_email" v-model="email"/>
            </b-input-group>
            <b-input-group class=mb-4>
                <b-input-group-prepend is-text><i class="fa fa-key" aria-hidden="true"></i></b-input-group-prepend>
                <b-input placeholder="Your password" type="password" :state="!invalid_password" v-model="password"/>
            </b-input-group>
        </b-form>
        <div class="text-center">
            <b-btn @click="login">Login</b-btn>
        </div>
    </div>
</template>

<script>
    export default {
        name: "LoginComponent",
        data(){
            return {
                email : "",
                password : "",
                errors : []
            }
        },
        methods: {
            login() {
                let email = this.email;
                let password = this.password;
                this.$store.dispatch('LOGIN', { email, password })
                    .then(() => this.$router.push('/'))
                    .catch(err => {
                        this.errors = err.response.data.errors
                    })
            }
        },
        computed: {
            invalid_email() {
                return this.errors && this.errors.email
            },
            invalid_password() {
                return this.errors && this.errors.password
            }
        }

    }
</script>

<style scoped>

</style>
