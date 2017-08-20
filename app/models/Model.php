<?php

/**
 * Defines common serialisation methods for all models
 */
class Model {

    /**
     * Store a reference to our collection
     */
    public $collection;

    public function __construct() {
        $this->collection = Database::collection(static::COLLECTION_NAME);
    }

    /**
     * Converts the model object into an array form, ready for sending/saving.
     * Excludes properties from the $exclude array
     */
    public function serialized($include_excluded = false) {

        $exclude = array_merge(
            $include_excluded ? [] : ($this->exclude ?? []),
            ['exclude', 'collection']
        );

        $instance_variables = get_object_vars($this);

        // If there are models inside this model, serialize those too

        array_walk($instance_variables, function(&$ivar) {
            $ivar = $ivar instanceof Model ? $ivar->serialized() : $ivar;
        });

        return array_diff_key($instance_variables, array_flip($exclude));

    }

    /**
     * Sets fillable properties from an array
     */
    public function fill_properties_from($properties) {
        foreach ($properties as $key => $value) {
            if (in_array($key, array_keys(get_object_vars($this)))) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Updates or inserts the model
     */
    public function save() {

        $primary_key = static::PRIMARY_KEY;

        $this->collection->updateOne(
            [$primary_key => $this->$primary_key],
            ['$set' => $this->serialized(true)],
            ['upsert' => true]
        );

    }

    /**
     * Deletes the model instance from the collection
     */
    public function delete() {
        $primary_key = static::PRIMARY_KEY;
        $this->collection->deleteOne([$primary_key => $this->$primary_key]);
    }

}

?>
