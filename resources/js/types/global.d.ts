import axios from 'axios';
import { route as ziggyRoute } from '../../../vendor/tightenco/ziggy/dist/index.js';

declare global {
    var route: typeof ziggyRoute;

    interface Window {
        axios: typeof axios;
        route: typeof ziggyRoute;
    }
}

export {};
