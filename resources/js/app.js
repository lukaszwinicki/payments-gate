import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import PaymentStatus from './components/PaymentStatus.vue';
import App from './App.vue';

const routes = [
  { path: '/payment-status', component: PaymentStatus }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

const app = createApp(App);
app.use(router);
app.mount('#app');
