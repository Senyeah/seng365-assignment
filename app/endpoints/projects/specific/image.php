<?php

require_once BASE_DIR . '/includes/helpers/UploadedImage.php';

require_once BASE_DIR . '/models/Project.php';
require_once BASE_DIR . '/models/ProjectImage.php';

class UpdateImage implements APIEngine\Requestable {

    use RequiresAuthentication;

	/**
     * @method PUT
     * @endpoint /projects/[id]/image
     */
	public function execute($request) {

        $project_id = intval($request->arguments['id']);
        $project = Project::from_id($project_id);

		// Make sure they're an creator of the project

		$creator_ids = array_map(function($creator) {
            return $creator->id;
		}, $project->creators);

		if (in_array($request->user->id, $creator_ids) == false) {
    		throw new APIError(403, 'Attempt to update a project you do not own');
		}

        // Do we have to delete the image?

		if (count($_FILES) == 0) {

            $project_image = ProjectImage::from_project_id($project_id);
            $project_image->delete();

            $project->imageUri = null;

		} else {

    		if (count($_FILES) > 1) {
    			throw new APIError(400, 'Cannot upload more than one file, ' . count($_FILES) . ' files given');
    		}

    		$image = new UploadedImage($_FILES['image']);

    		$project_image = new ProjectImage();

    		$project_image->project_id = $project_id;
    		$project_image->image_data = new MongoDB\BSON\Binary($image->png_data(), MongoDB\BSON\Binary::TYPE_GENERIC);

    		$project_image->save();

    		//Update the image URL

    		$project->imageUri = "http://$_SERVER[HTTP_HOST]/projects/$project_id/image";

		}

        $project->save();

	}

}

class RetrieveImage implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /projects/[id]/image
     */
	public function execute($request) {

        $project_id = intval($request->arguments['id']);
        $project_image = ProjectImage::from_project_id($project_id);

        header('Content-Type: image/jpeg');
        echo $project_image->image_data;

	}

}

?>
