import './bootstrap';
import { createApp } from 'vue';
import App from '@/App.vue';
import router from '@/router';

// Import Bootstrap CSS
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

// Create Vue app
const app = createApp(App);

// Use router
app.use(router);

// Mount app
app.mount('#app');
