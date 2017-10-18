import LoadingModal from './loading-modal';
import LoadingIndicator from './loading-indicator';
import ProgressIndicator from './progress-indicator';

angular.module('seng365-assignment.directives', ['seng365-assignment.services'])
    .directive('loadingModal', LoadingModal)
    .directive('loadingIndicator', LoadingIndicator)
    .directive('progressIndicator', ProgressIndicator);
