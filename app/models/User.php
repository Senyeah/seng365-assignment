<?php

require_once BASE_DIR . '/models/Model.php';

class User extends Model {

    // Let's hope we don't exceed 1 million users

    const ID_MAX = 1e6;

    const COLLECTION_NAME = 'Users';
    const PRIMARY_KEY = 'id';

    /**
     * Model properties
     */
    public $id;
    public $username;
    public $email;
    public $location;
    public $password;

    /**
     * Exclude these properties from being serialised
     */
    protected $exclude = [
        'password'
    ];

    /**
     * Creates a new user with passed model parameters
     */
    public static function new_from_model($params, $password) {

        $user = new self();
        $user->fill_properties_from($params);

        $user->id = $user->get_random_id();
        $user->set_password($password);

        return $user;

    }

    /**
     * Returns a user with a given identifier
     */
    public static function from_id($id) {

        if (self::exists_with('id', intval($id)) == false) {
            throw new APIError(404, 'User does not exist');
        }

        $user = new self();
        $user_document = $user->collection->findOne(['id' => intval($id)]);

        $user->fill_properties_from($user_document);
        $user->id = intval($id);

        return $user;

    }

    /**
     * Returns a user from a username and password if the credentials
     * supplied are valid
     */
    public static function from_authenticating($username, $password) {

        if (self::exists_with('username', $username) == false) {
            throw new APIError(400, 'Invalid username/password supplied');
        }

        $collection = Database::collection(self::COLLECTION_NAME);
        $user_document = $collection->findOne(['username' => $username]);

        if (password_verify($password, $user_document['password']) === false) {
            throw new APIError(400, 'Invalid username/password supplied');
        }

        return self::from_id($user_document['id']);

    }

    /**
     * Sets the password property to a hash of the provided plaintext password
     */
    public function set_password($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Gets a guaranteed-to-be-unique random identifier
     */
    private function get_random_id() {

        $new_id = mt_rand(1, self::ID_MAX);

        $id_vacant = empty($this->collection->find(['id' => $new_id])->toArray());
        return $id_vacant ? $new_id : $this->get_random_id();

    }

	/**
	 * Determines whether a given key exists in the users collection with a specific value
	 *
	 * @param string $key The key to check
	 * @param string $value The value that key must point to if it exists
	 * @return bool True if that key exists and points to the corresponding value
	 */
	public static function exists_with($key, $value) {
		$collection = Database::collection(self::COLLECTION_NAME);
		return is_null($collection->findOne([$key => $value])) == false;
	}

}

?>
