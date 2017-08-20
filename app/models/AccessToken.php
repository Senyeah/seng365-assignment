<?php

require_once BASE_DIR . '/models/Model.php';
require_once BASE_DIR . '/models/User.php';

class AccessToken extends Model {

    const COLLECTION_NAME = 'AccessTokens';
    const PRIMARY_KEY = 'token';

    /**
     * Model properties
     */
    public $id;
    public $token;

    /**
     * Generates an access token by authenticating a given username and password
     */
    public static function from_authenticating($username, $password) {

        $user = User::authenticate($username, $password);
        $token = new AccessToken();

        $token->token = bin2hex(openssl_random_pseudo_bytes(32));
        $token->id = $user->id;

        return $token;

    }

    /**
     * Returns an access token object from a provided token
     */
    public static function from_existing_token($token) {

        $collection = Database::collection(self::COLLECTION_NAME);
        $token_document = $collection->findOne(['token' => $token]);

        if (is_null($token_document)) {
            throw new APIError(401, 'Invalid access token provided');
        }

        $token = new AccessToken();
        $token->fill_properties_from($token_document);

        return $token;

    }

    /**
     * Returns an array of tokens for that user
     */
    public static function all_for_user($user) {

        $collection = Database::collection(self::COLLECTION_NAME);
        $tokens = [];

        foreach ($collection->find(['id' => $user->id]) as $token_document) {

            $token = new AccessToken();
            $token->fill_properties_from($token_document);

            $tokens[] = $token;

        }

        return $tokens;

    }

}

?>
