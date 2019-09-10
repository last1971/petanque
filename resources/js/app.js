/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import VueRouter from 'vue-router';
import Vuex from 'vuex';
import BootstrapVue from 'bootstrap-vue'

import router from './components/router';
import store from './components/store';


Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(BootstrapVue)

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('login-component', require('./components/LoginComponent.vue').default);
Vue.component('home-component', require('./components/HomeComponent').default)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router,
    store,
    created() {
        if (this.$store.getters.IS_LOGGEDIN) {
            window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
            this.$store.dispatch('SET_USER')
        }
    }

});
