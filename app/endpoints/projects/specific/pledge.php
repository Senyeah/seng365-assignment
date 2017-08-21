<?php

require_once BASE_DIR . '/models/Project.php';
require_once BASE_DIR . '/models/ProjectBacker.php';

class PledgeProject implements APIEngine\Requestable {

    use RequiresAuthentication;

    /**
     * @method POST
     * @endpoint /projects/[id]/pledge
     */
	public function execute($request) {

		$request->expect(
    		'id',
    		'amount',
    		'anonymous',
    		'card',
    		'card.authToken'
		);

        if (filter_var($_REQUEST['id'], FILTER_VALIDATE_INT) == false ||
            filter_var($_REQUEST['amount'], FILTER_VALIDATE_INT) == false) {

            throw new APIError(400, 'Amounts and IDs must be integers');
        }

        if (is_bool($_REQUEST['anonymous']) == false) {
            throw new APIError(400, 'Anonymous must be a boolean');
        }

        $project_id = intval($request->arguments['id']);
        $project = Project::from_id($project_id);

        $backer = new ProjectBacker();

        $backer->unserialize_from($_REQUEST);
        $backer->project_id = $project_id;

        $project->backers[] = $backer;

        $project->save();

	}

}

?>
