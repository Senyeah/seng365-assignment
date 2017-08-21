<?php

require_once BASE_DIR . '/models/Model.php';

class ProjectBacker extends Model {

    const COLLECTION_NAME = 'ProjectBackers';
    const PRIMARY_KEY = 'id';

    /**
     * Model properties
     */
    public $id;
    public $project_id;
    public $user_id;
    public $amount;
    public $anonymous;

    /**
     * Computed properties
     */
    public $name;

    /**
     * Hide from the serialisation
     */
    protected $hidden = [
        'project_id', 'id', 'user_id', 'anonymous'
    ];

    /**
     * Don't save in the database
     */
    protected $exclude = [
        'name'
    ];

    public static function new_from_model($model, $project_id) {

        $backer = new ProjectBacker();
        $backer->unserialize_from($model);

        $backer->project_id = $project_id;
        $backer->name = User::from_id($backer->user_id)->username;
        $backer->id = ProjectBacker::next_backer_id();

        return $backer;

    }

    /**
     * Returns stored project backers with a given project ID
     */
    public static function from_project_id($project_id) {

        $collection = Database::collection(self::COLLECTION_NAME);
        $backer_documents = $collection->find(['project_id' => $project_id])->toArray();

        return array_map(function($backer_document) {

            $backer = new ProjectBacker();
            $backer->unserialize_from($backer_document);

            $backer->name = User::from_id($backer->user_id)->username;

            return $backer;

        }, $backer_documents);

    }

    /**
     * Returns the next available backer ID
     */
    public static function next_backer_id() {

		$collection = Database::collection(self::COLLECTION_NAME);

		$sorted_backers = $collection->find([], ['sort' => ['id' => -1], 'limit' => 1])->toArray();
		$current_max_id = $sorted_backers[0]['id'] ?? 0;

		return $current_max_id + 1;

    }

}

?>
