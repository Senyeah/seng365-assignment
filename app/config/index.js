import configureRoutes from './routes';

/**
 * Configures ui-router states
 */
export function AppConfiguration($stateProvider, $urlRouterProvider, $locationProvider, $sceDelegateProvider) {

    'ngInject';

    // Configure the routes of the app

    configureRoutes($stateProvider, $urlRouterProvider, $locationProvider);

    // Finally allow resources to be loaded from the following domains

    $sceDelegateProvider.resourceUrlWhitelist([
        'self', 'http://localhost/**', 'https://*.canterbury.ac.nz/**'
    ]);

}
