import 'styles/directives/progress-indicator';

class ProgressIndicator {

    link($scope, $element, $attr) {
        $scope.$watch(
            () => [$attr.current, $attr.goal],
            ([current, goal]) => {
                const fractionPledged = Number(current) / Number(goal) * 100;
                $element.children().css('width', `${fractionPledged}%`);
            },
            true
        );
    }

    constructor() {

        this.link = angular.bind(this, this.link);

        this.restrict = 'E';
        this.replace = true;

        this.scope = {
            goal: '@',
            current: '@'
        };

        this.template = `
            <div class="progress-indicator">
                <div></div>
            </div>
        `;

    }

}

export default () => new ProgressIndicator();
