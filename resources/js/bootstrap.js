import axios from 'axios';
import 'livewire-sortable'
window.axios = axios;
// import function to register Swiper custom elements
import { register } from 'swiper/element/bundle';
// register Swiper custom elements
register();

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
