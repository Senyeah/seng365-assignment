import * as Controllers from './controllers';

export default ($stateProvider, $urlRouterProvider, $locationProvider) => {

    $stateProvider.state('app', {
        url: '/',
        templateUrl: 'controllers/main/main.html',
        controller: Controllers.Main,
        controllerAs: 'mainCtrl'
    });

    $locationProvider.html5Mode(true);
    $urlRouterProvider.otherwise('/');

}
