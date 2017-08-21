<?php

require_once BASE_DIR . '/models/Model.php';

require_once BASE_DIR . '/models/ProjectCreator.php';
require_once BASE_DIR . '/models/ProjectBacker.php';
require_once BASE_DIR . '/models/ProjectReward.php';

class Project extends Model {

    const COLLECTION_NAME = 'Projects';
    const PRIMARY_KEY = 'id';

    /**
     * Model properties
     */
    public $id;
    public $creation_date;

    public $title;
    public $subtitle;
    public $description;

    public $image_uri;
    public $target;
    public $creators;
    public $rewards;

    public $open;
    public $backers;

    /**
     * Exclude these properties from the schema
     */
    protected $exclude = [
        'creators',
        'rewards',
        'backers'
    ];

    /**
     * Exclude these properties from the serialization
     */
    protected $hidden = [
        'open'
    ];

    /**
     * Returns the next available project ID
     */
    public static function next_project_id() {

		$collection = Database::collection(self::COLLECTION_NAME);

		$sorted_projects = $collection->find([], ['sort' => ['id' => -1], 'limit' => 1])->toArray();
		$current_max_id = $sorted_projects[0]['id'] ?? 0;

		return $current_max_id + 1;

    }

    /**
     * Creates a new project from model parameters. Does not include
     * information about rewards, they must be added separately.
     */
    public static function new_from_model($model) {

        $project = new Project();

        $project->unserialize_from($model);

        //Some properties can't be manually set

        $project->id = self::next_project_id();
        $project->creation_date = time();

        //We'll set this, not the user

        $project->imageUri = '';

        $project->creators = [];
        $project->rewards = [];
        $project->backers = [];

        $project->open = true;

        return $project;

    }

    /**
     * Returns a stored project with a given project ID
     */
    public static function from_id($project_id) {

		$collection = Database::collection(self::COLLECTION_NAME);
		$project_document = $collection->findOne(['id' => $project_id]);

		if (is_null($project_document)) {
    		throw new APIError(404, 'Project not found');
		}

		$project = new Project();
		$project->unserialize_from($project_document);

		$project->creators = ProjectCreator::from_project_id($project_id) ?? [];
		$project->backers = ProjectBacker::from_project_id($project_id) ?? [];
		$project->rewards = ProjectReward::from_project_id($project_id) ?? [];

        return $project;

    }

    /**
     * Returns all projects within the given range
     */
    public static function all($start, $count) {

		$collection = Database::collection(self::COLLECTION_NAME);
		$project_document = $collection->find(['open' => true], ['limit' => $count, 'skip' => $start]);

		$projects = [];

		foreach ($project_document as $project) {
    		$projects[] = self::from_id($project['id'])->compact_response();
		}

		return $projects;

    }

    /**
     * Saves all foreign models and the current model
     */
    public function save() {

        $foreign_models = array_merge(
            $this->creators,
            $this->rewards,
            $this->backers
        );

        foreach ($foreign_models as $model) {
            $model->save();
        }

        parent::save();

    }

    /**
     * Gets the amount pledged by all backers
     */
    public function get_amount_pledged() {

        $total = array_map(function($backer) {
            return $backer->amount;
        }, $this->backers);

        return array_sum($total);

    }

    /**
     * Returns an array containing computed project progress
     */
    public function compute_progress() {
        return [
            'target' => $this->target,
            'currentPledged' => $this->get_amount_pledged(),
            'numberOfBackers' => count(array_filter($this->backers, function($backer) {
                return $backer->anonymous == false;
            }))
        ];
    }

    /**
     * Returns basic project information
     */
    public function compact_response() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'imageUri' => $this->image_uri
        ];
    }

    /**
     * Overrides the base serialisation to include some computed fields
     */
    public function formatted_reponse() {

        $project = parent::serialized();
        $backers = $project['backers'];

        //Several keys have to be moved under the `data` key

        $keys_to_move = array_diff(array_keys($project), ['id', 'creationDate', 'currentPledged', 'backers']);
        $project['data'] = [];

        foreach ($keys_to_move as $key) {
            $project['data'][$key] = $project[$key];
            unset($project[$key]);
        }

        $keys_to_remove = array_merge($keys_to_move, ['currentPledged', 'backers']);

        foreach ($keys_to_remove as $key) {
            unset($project[$key]);
        }

        return [
            'project' => $project,
            'progress' => $this->compute_progress(),
            'backers' => $backers
        ];

    }

}

?>
