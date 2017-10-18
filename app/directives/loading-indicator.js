import 'styles/directives/loading-indicator';

class LoadingIndicator {

    constructor() {

        this.restrict = 'E';
        this.replace = true;

        this.template = `
            <svg class="spinner" width="50px" height="50px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
               <circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle>
            </svg>
        `;

    }

}

export default () => new LoadingIndicator();
