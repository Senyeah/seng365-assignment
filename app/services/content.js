/**
 * This content allows controllers to queue promises which blocks the display
 * of content with a modal loading indicator until the queue is empty
 */
export class ContentLoadingService {

    get isLoading() {
        return this.numPending > 0;
    }

    updateBy(count) {
        this.numPending += count;
    }

    /**
     * Increments a counter until the promise is resolved
     */
    async addDeferred(promise) {

        this.updateBy(1);
        await promise;

        this.$rootScope.$apply(() => {
            this.updateBy(-1);
        });

    }

    constructor($rootScope) {

        'ngInject';

        this.numPending = 0;
        this.$rootScope = $rootScope;

        this.add = angular.bind(this, this.add);
        this.updateBy = angular.bind(this, this.updateBy);

    }

}
