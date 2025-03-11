require('./bootstrap');

import { createApp } from 'vue';
import UserManagement from './components/UserManagement.vue';

const app = createApp({});
app.component('user-management', UserManagement);
app.mount('#app');
