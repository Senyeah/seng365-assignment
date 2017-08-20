<?php

require_once BASE_DIR . '/engine/runtime.php';

class DeleteUser implements APIEngine\Requestable {

    /**
     * @method DELETE
     * @endpoint /users/[id]
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

class CreateUser implements APIEngine\Requestable {

    /**
     * @method POST
     * @endpoint /users
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

class RetrieveUser implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /users/[id]
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

class UpdateUser implements APIEngine\Requestable {

    /**
     * @method PUT
     * @endpoint /users/[id]
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

?>
