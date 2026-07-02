import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import 'bootstrap';
import '../css/style.css';
import '../css/app.css';
import { VueSpinnersPlugin } from 'vue3-spinners';



import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";



// import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'

// import 'bootstrap/dist/css/bootstrap.css'



import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'



const options = {
    transition: "Vue-Toastification__bounce",
    maxToasts: 20,
    newestOnTop: true
};


const app = createApp();

app.use(Toast, options);

// app.use(BootstrapVue)
// app.use(BootstrapVueIcons)

app.use(createPinia())
app.use(VueSpinnersPlugin);
app.use(router)
app.mount('#app')