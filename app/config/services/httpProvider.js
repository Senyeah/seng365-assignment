/**
 * Modifies the $http service to allow URLs like /projects
 * Works by prepending the base url to all requests which start with a slash
 */
export default ($httpProvider, Environment) => {

    'ngInject';

    // Allow $http to infer API URL

    $httpProvider.interceptors.push(() => ({

        request: config => {
            // If it starts with a slash, we have to prepend the API base URL

            if (config.url[0] == '/') {
                config.url = Environment.api.baseUrl.replace(/\/$/, '') + config.url;
            }

            return config;
        }

    }));

}
