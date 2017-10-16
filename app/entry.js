import angular from 'angular';
import uiRouter from '@uirouter/angularjs';

import {AppConfiguration} from './config';

import '@uirouter/angularjs/release/stateEvents';

angular.module('seng365-assignment', [uiRouter])
    .config(AppConfiguration)
    .run();
