// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

import Vue from 'vue';
import vuetify from "./libs/vuetify";
import App from './App.vue';
require('./libs/GlobalComponents');

new Vue({
    components: { App },
    vuetify,
}).$mount('#app');
