<?php

require_once BASE_DIR . '/models/Model.php';

class ProjectImage extends Model {

    const COLLECTION_NAME = 'ProjectImages';
    const PRIMARY_KEY = 'project_id';

    /**
     * Model properties
     */
    public $project_id;
    public $image_data;

    /**
     * Hide from serialization
     */
    protected $hidden = [
        'project_id'
    ];

    /**
     * Returns stored project rewards with a given project ID
     */
    public static function from_project_id($project_id) {

        $collection = Database::collection(self::COLLECTION_NAME);
        $image_document = $collection->findOne(['project_id' => $project_id]);

        if (is_null($image_document)) {
            throw new APIError(404, 'Project image not found');
        }

        $image = new ProjectImage();

        $image->project_id = $project_id;
        $image->image_data = $image_document['image_data']->getData();

        return $image;

    }

}

?>
