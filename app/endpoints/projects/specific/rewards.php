<?php

require_once BASE_DIR . '/models/Project.php';
require_once BASE_DIR . '/models/ProjectReward.php';

class RetrieveRewards implements APIEngine\Requestable {

    /**
     * @method GET
     * @endpoint /projects/[id]/rewards
     */
	public function execute($request) {

		$project = Project::from_id(intval($request->arguments['id']));

		return array_map(function($reward) {
    		return $reward->serialized();
        }, $project->rewards);

	}

}

class UpdateRewards implements APIEngine\Requestable {

    use RequiresAuthentication;

    /**
     * @method PUT
     * @endpoint /projects/[id]/rewards
     */
	public function execute($request) {

		$request->expect(
    		'*.id',
    		'*.amount',
    		'*.description'
		);

        $project_id = intval($request->arguments['id']);
        $project = Project::from_id($project_id);

		// Make sure they're an creator of the project

		$creator_ids = array_map(function($creator) {
            return $creator->id;
		}, $project->creators);

		if (in_array($request->user->id, $creator_ids) == false) {
    		throw new APIError(403, 'Attempt to update a project you do not own');
		}

		// Check for duplicate IDs

		$ids = array_map(function($reward) {
    		return $reward['id'];
        }, $_REQUEST);

        if (count(array_unique($ids)) !== count($ids)) {
            throw new APIError(400, 'Duplicate reward IDs');
        }

        // Make sure the IDs and amounts are numeric, then get the reward model

        $reward_models = [];

        foreach ($_REQUEST as $reward) {
            if (filter_var($reward['id'], FILTER_VALIDATE_INT) == false ||
                filter_var($reward['amount'], FILTER_VALIDATE_INT) == false) {

                throw new APIError(400, 'Amounts and IDs must be integers');
            }

            $reward_model = new ProjectReward();

            $reward_model->unserialize_from($reward);
            $reward_model->project_id = $project_id;

            $reward_models[$reward_model->id] = $reward_model;
        }

        // If some IDs change, we may have to delete some old rewards

        $old_reward_ids = array_map(function($reward) {
            return $reward->id;
        }, $project->rewards);

        $reward_ids_to_delete = array_diff($old_reward_ids, array_keys($reward_models));

        foreach ($project->rewards as $reward) {
            if (in_array($reward->id, $reward_ids_to_delete)) {
                $reward->delete();
            }
        }

        $project->rewards = array_values($reward_models);
        $project->save();

	}

}

?>
