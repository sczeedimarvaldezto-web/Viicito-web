import './bootstrap';

// Import App Styles FIRST (before Bootstrap overrides)
import '@/../css/app.css';

// Then import Bootstrap and icons (they enhance but don't override dark theme)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

import { createApp } from 'vue';
import App from '@/App.vue';
import router from '@/router';

// Create Vue app
const app = createApp(App);

// Use router
app.use(router);

// Mount app
app.mount('#app');
