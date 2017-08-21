<?php

require_once BASE_DIR . '/models/Model.php';

class ProjectBacker extends Model {

    const COLLECTION_NAME = 'ProjectBackers';
    const PRIMARY_KEY = ['id', 'project_id'];

    /**
     * Model properties
     */
    public $project_id;
    public $id;
    public $amount;
    public $anonymous;

    protected $hidden = [
        'anonymous'
    ];

    public static function new_from_model($model, $project_id) {

        $backer = new ProjectBacker();
        $backer->unserialize_from($model);

        $backer->project_id = $project_id;

        return $backer;

    }

    /**
     * Returns stored project backers with a given project ID
     */
    public static function from_project_id($project_id) {

        $collection = Database::collection(self::COLLECTION_NAME);
        $backer_documents = $collection->find(['project_id' => $project_id])->toArray();

        return array_map(function($backer_document) {

            $backer = new ProjectReward();
            $backer->unserialize_from($backer_document);

            return $backer;

        }, $backer_documents);

    }

    /**
     * override the default behaviour so anonymous pledgers aren't visible
     */
    public function serialized($include_hidden = false, $to_camelcase = true) {

        $result = parent::serialized($include_hidden, $to_camelcase);

        if ($this->anonymous && $request->user->id != $this->id) {
            return [];
        }

        return $result;

    }

}

?>
