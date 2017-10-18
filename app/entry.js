import angular from 'angular';
import uiRouter from '@uirouter/angularjs';
import cookies from 'angular-cookies';

import HttpProvider from './config/services/httpProvider';

import { AppConfiguration } from './config';
import { AppRun } from './appRun';
import environment from 'json/env';

import '@uirouter/angularjs/release/stateEvents';

// Include global styles

import 'styles/global';

// Load other modules of the app

import './directives';
import './services';
import './filters';

angular.module(
        'seng365-assignment',
        [
            uiRouter,
            cookies,
            'seng365-assignment.directives',
            'seng365-assignment.services',
            'seng365-assignment.filters',
        ],
        HttpProvider
    )
    .constant('Environment', environment)
    .config(AppConfiguration)
    .run(AppRun);
