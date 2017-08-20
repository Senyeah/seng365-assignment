<?php

require_once BASE_DIR . '/engine/runtime.php';

class RetrieveAllProjects implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /projects
     */
	public function execute($request) {
		phpinfo();
	}

}

?>
