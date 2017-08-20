<?php

require_once BASE_DIR . '/engine/runtime.php';

class RetrieveAllProjects implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /projects
     */
	public function execute($request) {

		$manager = new MongoDB\Driver\Manager(MONGODB_SERVER_URI);
        $collection = new MongoDB\Collection($manager, 'test', 'testCollection');

        $collection->insertOne([
            'time' => time()
        ]);

        echo '<pre>';

        foreach ($collection->find() as $res) {
            echo 'time: ' . $res['time'] . '<br>';
        }

	}

}

?>
