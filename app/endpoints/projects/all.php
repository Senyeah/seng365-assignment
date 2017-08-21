<?php

require_once BASE_DIR . '/models/Project.php';

class RetrieveAllProjects implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /projects
     */
	public function execute($request) {

        $request->expect('startIndex', 'count');
		return Project::all(intval($_REQUEST['startIndex']), intval($_REQUEST['count']));

	}

}

?>
