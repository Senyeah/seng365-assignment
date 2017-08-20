<?php

/**
 * Database.php: Provides convienience methods for accessing collections
 */
class Database {

    static function collection($name) {
        $manager = new MongoDB\Driver\Manager(MONGODB_SERVER_URI);
        return new MongoDB\Collection($manager, MONGODB_DATABASE_NAME, $name);
    }

}

?>
