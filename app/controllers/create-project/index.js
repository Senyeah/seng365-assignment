import 'styles/controllers/create-project';

class CreateProjectViewController {

    static TEMPLATE_REWARD = {
        description: '',
        amount: null
    };

    get isValid() {
        return this.project.title &&
                this.project.subtitle &&
                this.project.description &&
                this.project.target &&
                this.project.rewards[0].description &&
                this.project.rewards[0].amount;
    }

    addReward() {
        this.project.rewards.push(
            angular.copy(CreateProjectViewController.TEMPLATE_REWARD)
        );
    }

    async createProject() {

        // Discard empty rewards and convert amounts to cents

        this.project.rewards = this.project.rewards.filter(
            reward => reward.description && reward.amount
        ).map(
            reward => ({
                description: reward.description,
                amount: reward.amount * 100
            })
        );

        this.project.target *= 100;

        const createPromise = this.Project.create(this.project)

        // Show the loading screen while it's being uploaded

        this.Content.addDeferred(createPromise);
        const id = await createPromise;

        // Now upload the image if there is one

        const formData = new FormData(document.querySelector('form'));
        await this.Project.uploadImage(id, formData);

        this.$state.go('project', {id});

    }

    constructor(Content, Project, User, $state, $rootScope) {

        'ngInject';

        $rootScope.title = 'Create Project';

        this.Project = Project;
        this.Content = Content;
        this.$state = $state;

        this.addReward = angular.bind(this, this.addReward);

        /**
         * Must be authenticated to create a project
         */
        User.isLoggedIn == false && $state.go('main');

        /**
         * Project model
         */
        this.project = {
            title: '',
            subtitle: '',
            description: '',
            target: null,
            rewards: [
                angular.copy(CreateProjectViewController.TEMPLATE_REWARD)
            ]
        }

    }

}

export { CreateProjectViewController as CreateProject };
