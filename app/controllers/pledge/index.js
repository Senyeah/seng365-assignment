import 'styles/controllers/pledge';

class PledgeViewController {

    async pay() {

        const authToken = Math.random().toString(36).substring(7);

        await this.$http.post(`/projects/${this.projectId}/pledge`, {
            id: this.User.current.id,
            amount: this.amount * 100,
            anonymous: this.isAnonymous,
            card: {authToken}
        });

        this.$state.go('^.', {}, {reload: true});

    }

    constructor(User, Content, $scope, $state, $q, $http) {

        'ngInject'

        this.$http = $http;
        this.$state = $state;
        this.User = User;

        this.pay = angular.bind(this, this.pay);

        // Must be authenticated to pledge

        User.isLoggedIn == false && $state.go('^.');

        // Show the loading screen until we have info about the project

        const loadProjectId = $q.defer();

        $scope.$watch(
            () => $scope.$parent.projectCtrl.project,
            project => {
                if (project) {
                    loadProjectId.resolve();
                    this.projectId = project.id;
                }
            },
            true
        );

        Content.addDeferred(loadProjectId.promise);

        // Set initial values to allow placeholders to show through

        this.amount = '';
        this.card = '';
        this.projectId = null;

        this.isAnonymous = false;

    }

}

export { PledgeViewController as Pledge };
