export class ProjectService {

    static MAX_RESULTS_PER_PAGE = 6;

    /**
     * Performs a search for projects. If the query is empty, all projects are returned.
     */
    async search({query = '', page = 0, mode = 'all'}) {

        // Only include the params in the event there's no query

        const params = {
            startIndex: page * ProjectService.MAX_RESULTS_PER_PAGE,
            count: ProjectService.MAX_RESULTS_PER_PAGE
        };

        if (mode == 'backed') {
            params.backer = this.User.current.id;
        } else if (mode == 'created') {
            params.creator = this.User.current.id;
        }

        const response = await this.$http.get('/projects', query ? {} : {params});

        // Perform filtering if there was a query

        const contains = (string, query) => {
            if (query.length) {
                return string.toLowerCase().split(' ').some(
                    word => query.toLowerCase().split(' ').includes(word)
                );
            } else {
                return true;
            }
        }

        return response.data.filter(
            project => project.open && contains(project.title, query) || contains(project.subtitle, query)
        )

    }

    /**
     * Creates a project and returns its new identifier
     */
    async create(params) {

        params.creators = [{
            id: this.User.current.id
        }];

        const response = await this.$http.post('/projects', params);
        return response.data.id;

    }

    /**
     * Loads a specific project id
     */
    async load(id) {
        const response = await this.$http.get(`/projects/${id}`);
        return response.data;
    }

    /**
     * Closes a given project ID when its owner clicks the button
     */
    async close(id) {
        await this.$http.put(`/projects/${id}`, {
            open: false
        });
    }

    /**
     * Uploads an image to a given project ID
     */
    async uploadImage(id, formData) {

        await this.$http.put(`/projects/${id}`, formData);

    }

    constructor(User, $http) {

        'ngInject';

        this.$http = $http;
        this.User = User;
        this.search = angular.bind(this, this.search);

    }

}
