const ToImageUrlFilter = Environment => {
    'ngInject';
    return baseUri => baseUri ? `${Environment.api.baseUrl}${baseUri}` : '';
}

angular.module('seng365-assignment.filters', [])
    .filter('toImageUrl', ToImageUrlFilter);
