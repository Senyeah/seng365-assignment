<?php

require_once BASE_DIR . '/models/User.php';
require_once BASE_DIR . '/models/Project.php';
require_once BASE_DIR . '/models/ProjectCreator.php';
require_once BASE_DIR . '/models/ProjectReward.php';

class CreateProject implements APIEngine\Requestable {

    use RequiresAuthentication;

    /**
     * @method POST
     * @endpoint /projects
     */
	public function execute($request) {

		$request->expect(
    	    'title',
    	    'subtitle',
    	    'description',
    	    'imageUri',
    	    'target',
    	    'creators',
    	    'creators.*.id',
    	    'creators.*.name',
    	    'rewards',
    	    'rewards.*.id',
    	    'rewards.*.amount',
    	    'rewards.*.description'
		);

		if (filter_var($_REQUEST['target'], FILTER_VALIDATE_INT) == false) {
    		throw new APIError(400, 'Target must be an integer');
		}

        // Validate the creators

        $creator_user_ids = array_map(function($creator) {
            return User::from_id($creator['id'])->id;
        }, $_REQUEST['creators']);

        // We have to be in the creators array

        if (in_array(intval($request->user->id), $creator_user_ids) == false) {
            throw new APIError(400, 'Attempt to create a project without being a creator');
        }

        // Validate the rewards
        foreach ($_REQUEST['rewards'] as $reward) {
            if (filter_var($reward['amount'], FILTER_VALIDATE_INT) == false) {
        		throw new APIError(400, 'Reward amount must be an integer');
    		}
        }

        // Check for duplicate reward IDs
		$ids = array_map(function($reward) {
    		return $reward['id'];
        }, $_REQUEST['rewards']);

        if (count(array_unique($ids)) !== count($ids)) {
            throw new APIError(400, 'Duplicate reward IDs');
        }

        // Create the Project model
        $project = Project::new_from_model($_REQUEST);

        // Create the models for creators
        $project->creators = array_map(function($creator) use ($project) {
            return ProjectCreator::new_from_model($creator, $project->id);
        }, $_REQUEST['creators']);

        // ...and for the rewards
        $project->rewards = array_map(function($reward) use ($project) {
            return ProjectReward::new_from_model($reward, $project->id);
        }, $_REQUEST['rewards']);

        // Now save the entire thing to the database
        $project->save();

        http_response_code(201);

	}

}

class RetrieveProject implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /projects/[id]
     */
	public function execute($request) {

		$project = Project::from_id(intval($request->arguments['id']));
        $project->current_user_id = $request->user->id ?? null;

		return $project->formatted_reponse();

	}

}

class UpdateProject implements APIEngine\Requestable {

    use RequiresAuthentication;

    /**
     * @method PUT
     * @endpoint /projects/[id]
     */
	public function execute($request) {

		$request->expect('open');

        $project_id = intval($request->arguments['id']);
        $project = Project::from_id($project_id);

		// Make sure they're an creator of the project

		$creator_ids = array_map(function($creator) {
            return $creator->id;
		}, $project->creators);

		if (in_array($request->user->id, $creator_ids) == false) {
    		throw new APIError(403, 'Attempt to update a project you do not own');
		}

		// Make sure what we were given is a boolean

		if (is_bool($_REQUEST['open']) == false) {
    		throw new APIError(400, 'Open must be a boolean');
		}

		// Now we'll update the open status

		$project->open = $_REQUEST['open'];

		$project->save();
		http_response_code(201);

	}

}

?>
