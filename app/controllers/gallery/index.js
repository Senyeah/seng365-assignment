import 'styles/controllers/gallery';

class GalleryViewController {

    get hasModifiedQuery() {
        return this.query != this.initialQuery;
    }

    get headerTitle() {
        return this.query ? 'Search Results' : 'All Projects';
    }

    get page() {
        return this.$state.params.page ? parseInt(this.$state.params.page) : 1;
    }

    async loadProjects() {
        this.projects = await this.Project.search({
            /**
             * Don't show a zero-indexed page in the URL
             */
            page: this.page - 1,
            query: this.query,
            mode: this.filterMode
        });

        this.$scope.$apply();
    }

    search({query}) {
        this.$state.go('main', query ? {query} : {}, {inherit: false});
    }

    constructor(Content, Project, $rootScope, $scope, $state) {

        'ngInject';

        this.Content = Content;
        this.Project = Project;
        this.$scope = $scope;
        this.$state = $state;

        this.loadProjects = angular.bind(this, this.loadProjects);
        this.search = angular.bind(this, this.search);

        this.projects = [];

        this.initialQuery = $state.params.query || '';
        this.query = this.initialQuery;

        $rootScope.title = this.headerTitle;

        // If they're wanting to refine their filter, make the appropriate button filled

        this.filterMode = $state.params.only || 'all';

        // Load the projects

        Content.addDeferred(this.loadProjects());

    }

}

export { GalleryViewController as Gallery };
