export class UserService {

    /**
     * If we have an access token, this is true
     */
    get isLoggedIn() {
        return !!this.accessToken;
    }

    /**
     * Returns the cookie expiry date
     */
    get cookieExpiry() {
        const ONE_WEEK_IN_SECONDS = 604.8e6;
        return new Date((new Date).getTime() + 4 * ONE_WEEK_IN_SECONDS);
    }

    /**
     * Reads the access token from the cookie
     */
    get accessToken() {
        return this.$cookies.get('token');
    }

    /**
     * Sets the token cookie with the provided token.
     */
    set accessToken(token) {

        if (token) {
            this.$cookies.put('token', token, {expires: this.cookieExpiry});
            this.$http.defaults.headers.common['X-Authorization'] = token;
        } else {
            this.$cookies.remove('token');
            delete this.$http.defaults.headers.common['X-Authorization'];
        }

    }

    /**
     * Sets the user ID as a cookie
     */
    set userId(id) {

        if (id) {
            this.$cookies.put('userId', id, {expires: this.cookieExpiry});
        } else {
            this.$cookies.remove('userId');
        }

    }

    /**
     * Gets the user ID from the cookie
     */
    get userId() {
        return this.$cookies.get('userId');
    }

    async login(params) {

        const query = this.$httpParamSerializer(params);

        const response = await this.$http.post(`/users/login?${query}`);
        const {id, token} = response.data;

        this.current = {id, token};

        this.userId = id;
        this.accessToken = token;

    }

    logout() {
        this.$http.post('/users/logout');
        this.accessToken = null;
        this.$state.go('main');
    }

    async create(params) {
        await this.$http.post('/users', params);
    }

    /**
     * Sets the access token cookie as the header if necessary
     */
    async init() {

        if (this.accessToken) {

            this.$http.defaults.headers.common['X-Authorization'] = this.accessToken;

            this.current = {
                id: parseInt(this.userId),
                token: this.accessToken
            };

        }

    }

    constructor($http, $cookies, $state, $httpParamSerializer) {

        'ngInject';

        this.$http = $http;
        this.$cookies = $cookies;
        this.$state = $state;
        this.$httpParamSerializer = $httpParamSerializer;

        this.current = null;
        this.login = angular.bind(this, this.login);

    }

}
