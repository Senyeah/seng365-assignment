import 'styles/controllers/sign-in';

class SignInViewController {

    get isValid() {
        return this.user.email && this.user.password;
    }

    async submit() {

        try {

            const {email, password} = this.user;

            // This is absolutely stupid why do I have to set the username
            // to their email wtf

            await this.User.login({
                email, password,
                username: email
            });

            this.$state.go('main');

        } catch (e) {
            this.error = e.statusText;
        } finally {
            this.$scope.$apply();
        }

    }

    constructor(User, $rootScope, $scope, $state) {

        'ngInject';

        this.User = User;
        this.$scope = $scope;
        this.$state = $state;

        $rootScope.title = 'Sign In';

        this.submit = angular.bind(this, this.submit);
        this.error = '';

        this.user = {
            email: '',
            password: ''
        };

    }

}

export { SignInViewController as SignIn };
