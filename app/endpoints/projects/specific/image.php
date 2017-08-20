<?php

require_once BASE_DIR . '/engine/runtime.php';

class UpdateImage implements APIEngine\Requestable {

	/**
     * @method PUT
     * @endpoint /projects/[id]/image
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

class RetrieveImage implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /projects/[id]/image
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

?>
