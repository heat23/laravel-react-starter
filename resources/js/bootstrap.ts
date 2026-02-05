import axios from 'axios';
import { route as ziggyRoute } from 'ziggy-js';

import { Ziggy } from './ziggy.js';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set default timeout for all axios requests (30 seconds)
window.axios.defaults.timeout = 30000;

// Make Ziggy route function available globally
window.route = (name, params, absolute, config = Ziggy) => {
    return ziggyRoute(name, params, absolute, config);
};
