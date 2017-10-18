/**
 * Allows the user service to be accessed anywhere, also sets X-Authorization
 * header from cookie if one is set
 */
export function AppRun(User, $rootScope) {

    'ngInject';

    $rootScope.User = User;
    User.init();

}
