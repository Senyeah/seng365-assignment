<?php

require_once BASE_DIR . '/models/Model.php';

class ProjectCreator extends Model {

    const COLLECTION_NAME = 'ProjectCreators';
    const PRIMARY_KEY = ['id', 'project_id'];

    /**
     * Model properties
     */
    public $project_id;
    public $id;
    public $name;

    /**
     * Hide from serialization
     */
    protected $hidden = [
        'project_id'
    ];

    public static function new_from_model($model, $project_id) {

        $creator = new ProjectCreator();
        $creator->unserialize_from($model);

        $creator->project_id = $project_id;

        return $creator;

    }

    /**
     * Returns stored project creators with a given project ID
     */
    public static function from_project_id($project_id) {

        $collection = Database::collection(self::COLLECTION_NAME);
        $creator_documents = $collection->find(['project_id' => $project_id])->toArray();

        return array_map(function($creator_document) {

            $creator = new ProjectCreator();
            $creator->unserialize_from($creator_document);

            return $creator;

        }, $creator_documents);

    }

}

?>
