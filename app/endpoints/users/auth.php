<?php

require_once BASE_DIR . '/engine/runtime.php';

class LogoutUser implements APIEngine\Requestable {

    /**
     * @method POST
     * @endpoint /users/logout
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

class LoginUser implements APIEngine\Requestable {

    /**
     * @method POST
     * @endpoint /users/login
     */
	public function execute($request) {
		//Implement code to run upon this endpoint being called
	}

}

?>
