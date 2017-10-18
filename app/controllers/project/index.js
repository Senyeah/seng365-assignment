import 'styles/controllers/project';

class ProjectViewController {

    async load(id) {

        this.project = await this.Project.load(id);
        this.$rootScope.title = this.project.title;

        // Figure out who's unique

        const users = [...new Set(this.project.backers.map(backer => backer.username))];

        this.uniqueBackers = users.map(
            user => this.project.backers.filter(backer => backer.username == user)
        ).map(
            userPledges => userPledges.reduce(
                (total, current) => ({
                    username: current.username,
                    amount: total.amount + current.amount
                }), {
                    amount: 0
                }
            )
        );

        // Does the current user own it?

        this.currentUserOwnsProject = this.User.isLoggedIn &&
                                      this.project.creators
                                            .map(creator => parseInt(creator.id))
                                            .includes(this.User.current.id);

    }

    /**
     * Closes a given project when the current user owns it
     */
    async close() {
        await this.Project.close(this.$state.params.id);
        this.$state.go('.', {}, {reload: true});
    }

    async updateImage() {

        const formData = new FormData(document.querySelector('form'));
        await this.Project.uploadImage(this.$state.params.id, formData.get('image'));

        this.$state.go('.', {}, {reload: true});

    }

    constructor(Project, Content, User, $rootScope, $scope, $state) {

        'ngInject';

        this.$rootScope = $rootScope;
        this.$scope = $scope;
        this.$state = $state;

        this.User = User;
        this.Project = Project;

        this.load = angular.bind(this, this.load);

        this.project = null;
        this.uniqueBackers = [];
        this.currentUserOwnsProject = false;

        Content.addDeferred(this.load($state.params.id));

    }

}

export { ProjectViewController as Project };
