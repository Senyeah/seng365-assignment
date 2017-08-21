<?php

require_once BASE_DIR . '/models/Model.php';

class ProjectReward extends Model {

    const COLLECTION_NAME = 'ProjectRewards';
    const PRIMARY_KEY = ['id', 'project_id'];

    /**
     * Model properties
     */
    public $project_id;
    public $id;
    public $amount;
    public $description;

    /**
     * Hide from serialization
     */
    protected $hidden = [
        'project_id'
    ];

    public static function new_from_model($model, $project_id) {

        $reward = new ProjectReward();
        $reward->unserialize_from($model);

        $reward->project_id = $project_id;

        return $reward;

    }

    /**
     * Returns stored project rewards with a given project ID
     */
    public static function from_project_id($project_id) {

        $collection = Database::collection(self::COLLECTION_NAME);
        $reward_documents = $collection->find(['project_id' => $project_id])->toArray();

        return array_map(function($reward_document) {

            $reward = new ProjectReward();
            $reward->unserialize_from($reward_document);

            return $reward;

        }, $reward_documents);

    }

}

?>
