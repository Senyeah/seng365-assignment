<?php

require_once BASE_DIR . '/engine/runtime.php';
require_once BASE_DIR . '/models/User.php';

class CreateUser implements APIEngine\Requestable {

    /**
     * @method POST
     * @endpoint /users
     */
	public function execute($request) {

		$request->expect(
		    'user',
		    'user.id',
		    'user.username',
		    'user.location',
		    'user.email',
		    'password'
        );

        // Validate the email address

        if (filter_var($_REQUEST['user']['email'], FILTER_VALIDATE_EMAIL) == false) {
            throw new APIError(400, 'Invalid email address');
        }

        // Ensure the username and email is not taken

        if (User::exists_with('email', $_REQUEST['user']['email']) ||
            User::exists_with('username', $_REQUEST['user']['username'])) {

            throw new APIError(400, 'Email and username must be unique');
        }

        $user = User::new_from_model($_REQUEST['user'], $_REQUEST['password']);
        $user->save();

        http_response_code(201);

	}

}

class RetrieveUser implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /users/[id]
     */
	public function execute($request) {

		// Validate the ID

		if (filter_var($request->arguments['id'], FILTER_VALIDATE_INT) == false) {
    		throw new APIError(400, 'Invalid user ID provided');
		}

		return User::from_id($request->arguments['id']);

	}

}

class UpdateUser implements APIEngine\Requestable {

    public $requires_authentication = true;

    /**
     * @method PUT
     * @endpoint /users/[id]
     */
	public function execute($request) {

    	$request->expect(
		    'user',
		    'user.id',
		    'user.username',
		    'user.location',
		    'user.email',
		    'password'
        );

        //Check the ID provided is this account

    	$provided_user = User::from_id($request->arguments['id']);

    	if ($provided_user->id != $request->user->id) {
        	throw new APIError(403, 'Forbidden to update another account');
    	}

        // Validate the email address

        if (filter_var($_REQUEST['user']['email'], FILTER_VALIDATE_EMAIL) == false) {
            throw new APIError(400, 'Invalid email address');
        }

        // Ensure the username and email is not taken OR is taken by us

        if (User::exists_with('email', $_REQUEST['user']['email']) &&
            $request->user->email != $_REQUEST['user']['email']) {

            throw new APIError(400, 'Email must be unique');
        }

        if (User::exists_with('username', $_REQUEST['user']['username']) &&
            $request->user->username != $_REQUEST['user']['username']) {

            throw new APIError(400, 'Username must be unique');
        }

    	$request->user->fill_properties_from($_REQUEST['user']);

    	$request->user->set_password($_REQUEST['password']);
    	$request->user->id = intval($provided_user->id);

    	$request->user->save();

	}

}

class DeleteUser implements APIEngine\Requestable {

    public $requires_authentication = true;

    /**
     * @method DELETE
     * @endpoint /users/[id]
     */
	public function execute($request) {

        //Check the ID provided is this account

    	$user_from_id = User::from_id($request->arguments['id']);

    	if ($user_from_id->id != $request->user->id) {
        	throw new APIError(403, 'Forbidden to delete another account');
    	}

    	// Delete their access tokens...

    	foreach (AccessToken::all_for_user($request->user) as $token) {
        	$token->delete();
    	}

    	// ...and then the user

		$request->user->delete();

	}

}

?>
