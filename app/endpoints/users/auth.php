<?php

require_once BASE_DIR . '/engine/runtime.php';
require_once BASE_DIR . '/models/AccessToken.php';

class LogoutUser implements APIEngine\Requestable {

    public $requires_authentication = true;

    /**
     * @method POST
     * @endpoint /users/logout
     */
	public function execute($request) {
        $request->access_token->delete();
	}

}

class LoginUser implements APIEngine\Requestable {

    /**
     * @method POST
     * @endpoint /users/login
     */
	public function execute($request) {

        $request->expect('username', 'password');

        $token = AccessToken::from_authenticating($_REQUEST['username'], $_REQUEST['password']);
        $token->save();

        return $token;

	}

}

?>
