import 'styles/directives/loading-modal';

class LoadingModal {

    link($scope, $element, $attr) {
        $scope.Content = this.Content;
    }

    constructor(Content) {

        this.Content = Content;
        this.link = angular.bind(this, this.link);

        this.restrict = 'E';
        this.replace = true;

        this.template = `
            <div class="loading-modal" ng-if="Content.isLoading">
                <loading-indicator></loading-indicator>
            </div>
        `;

    }

}

export default Content => {
    'ngInject';
    return new LoadingModal(Content);
}
