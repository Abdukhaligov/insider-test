import './bootstrap';
import { createApp } from 'vue'
import 'bootstrap';
import { createRouter, createWebHistory } from 'vue-router';

import App from './Components/App.vue';
import Dashboard from './Pages/Dashboard.vue';
import SeasonsView from './Pages/Seasons/View.vue';
import SeasonsCreate from "./Pages/Seasons/Create.vue";

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faPlus, faEye } from '@fortawesome/free-solid-svg-icons'

library.add(faPlus, faEye)

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: Dashboard },
        { path: '/seasons/create', component: SeasonsCreate },
        { path: '/seasons/:id', component: SeasonsView },
    ]
});

const app = createApp(App)
    .use(router)
    .component('font-awesome-icon', FontAwesomeIcon)
    .mount('#app');

