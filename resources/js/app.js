import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { useAuthStore } from './stores/auth';

import '../css/app.css';
import 'jsvectormap/dist/jsvectormap.css';

const app = createApp(App);
const pinia = createPinia();

import VueApexCharts from 'vue3-apexcharts';

app.use(pinia);
app.use(router);
app.use(VueApexCharts);

// Initialize auth
const authStore = useAuthStore();
authStore.initAuth(router);

app.mount('#app');
