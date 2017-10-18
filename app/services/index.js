import { ContentLoadingService } from './content';
import { ProjectService } from './projects';
import { UserService } from './user';

angular.module('seng365-assignment.services', [])
    .service('Content', ContentLoadingService)
    .service('Project', ProjectService)
    .service('User', UserService);
