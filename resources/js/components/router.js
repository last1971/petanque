import VueRouter from 'vue-router';
import store from './store'

const LoginComponent = require('./LoginComponent').default
const HomeComponent = require('./HomeComponent').default
const EventComponent = require('./EventComponent').default
const RoundComponent = require('./RoundComponent').default

const routes = [
    { name: 'home', path: '/', component: HomeComponent, meta: { requiresAuth: true, title: 'Главная' } },
    { name: 'login', path: '/login', component: LoginComponent, meta: { title: 'Вход' } },
    { name: 'event', path: '/event/:id', component: EventComponent, meta: { requiresAuth: true, title: 'Событие' } },
    { name: 'round', path: '/round/:id', component: RoundComponent,  meta: { requiresAuth: true, title: 'Раунд' } }
]

var router = new VueRouter({
    mode: 'history',
    routes
});


router.beforeEach((to, from, next) => {
    document.title = to.meta.title
    if(to.matched.some(record => record.meta.requiresAuth)) {
        if (store.getters.IS_LOGGEDIN) {
            next();
            return
        }
        next('/login');
    } else {
        next();
    }
});

export default router;
