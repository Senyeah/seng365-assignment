import * as Controllers from './controllers';

export default ($stateProvider, $urlRouterProvider, $locationProvider) => {

    $stateProvider.state('main', {
        url: '/?query&page&only',
        views: {
            '': {
                controller: Controllers.Main,
                controllerAs: 'mainCtrl',
                templateUrl: 'controllers/main/index.html'
            },
            'projects@main': {
                controller: Controllers.Gallery,
                controllerAs: 'galleryCtrl',
                templateUrl: 'controllers/gallery/index.html'
            }
        }
    })
    .state('project', {
        url: '/project/:id',
        controller: Controllers.Project,
        controllerAs: 'projectCtrl',
        templateUrl: 'controllers/project/index.html'
    })
    .state('project.pledge', {
        url: '/pledge',
        views: {
            'modal@project': {
                controller: Controllers.Pledge,
                controllerAs: 'pledgeCtrl',
                templateUrl: 'controllers/pledge/index.html'
            }
        }
    })
    .state('sign-in', {
        url: '/sign-in',
        controller: Controllers.SignIn,
        controllerAs: 'signInCtrl',
        templateUrl: 'controllers/sign-in/index.html'
    })
    .state('sign-up', {
        url: '/sign-up',
        controller: Controllers.SignUp,
        controllerAs: 'signUpCtrl',
        templateUrl: 'controllers/sign-up/index.html'
    })
    .state('create-project', {
        url: '/create-project',
        controller: Controllers.CreateProject,
        controllerAs: 'createProjectCtrl',
        templateUrl: 'controllers/create-project/index.html'
    });

    $locationProvider.html5Mode(true);
    $urlRouterProvider.otherwise('/');

}
