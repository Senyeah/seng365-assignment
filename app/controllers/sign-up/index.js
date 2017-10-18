class SignUpViewController {

    get isValid() {

        return this.user.username &&
               this.user.email &&
               this.user.password;

    }

    async submit() {

        const {email, password} = this.user;
        this.buttonText = 'Loading…';

        if (!this.user.location) {
            delete this.user.location;
        }

        try {

            await this.User.create(this.user);
            await this.User.login({email, password, username: email});

            this.$state.go('main');

        } catch (e) {

            this.error = e.statusText;
            this.buttonText = 'Create Account';

        } finally {
            this.$scope.$apply();
        }

    }

    constructor(User, $rootScope, $scope, $state) {

        'ngInject'

        this.User = User;
        this.$scope = $scope;
        this.$state = $state;

        this.submit = angular.bind(this, this.submit);

        this.buttonText = $rootScope.title = 'Create Account';
        this.error = '';

        this.user = {
            username: '',
            email: '',
            password: '',
            location: ''
        };

    }

}

export { SignUpViewController as SignUp };
