<?php

require_once BASE_DIR . '/engine/runtime.php';

class UpdateProject implements APIEngine\Requestable {

    /**
     * @method PUT
     * @endpoint /projects/[id]
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

class RetrieveProject implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /projects/[id]
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

class CreateProject implements APIEngine\Requestable {

    /**
     * @method POST
     * @endpoint /projects
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

?>
