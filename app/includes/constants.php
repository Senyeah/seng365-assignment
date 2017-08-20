<?php

/**
 * constants.php: Defines constants which are globally available in any
 * any file.
 */

// The path to the redirect tree, the file which determines which class to
// invoke given an arbritary URL

define('REDIRECT_TREE_PATH', BASE_DIR . '/.definition.json');

// Defines the MongoDB server location

define("MONGODB_SERVER_URI", 'mongodb://mongo:27017');

?>
