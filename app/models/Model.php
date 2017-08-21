<?php

/**
 * Defines common serialisation methods for all models
 */
class Model {

    /**
     * Attributes which should never be saved
     */
    const ATTRIBUTES_ALWAYS_HIDDEN = ['hidden', 'collection', 'exclude'];

    /**
     * Store a reference to our collection
     */
    public $collection;

    public function __construct() {
        $this->collection = Database::collection(static::COLLECTION_NAME);
    }

    /**
     * Converts the model object into an array form, ready for sending/saving.
     * Excludes properties from the $hidden array
     */
    public function serialized($include_hidden = false) {

        $exclude = array_merge(
            $include_hidden ? [] : ($this->hidden ?? []),
            self::ATTRIBUTES_ALWAYS_HIDDEN
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
        $unique_attributes = [];

        // Primary keys may contain more than one attribute

        if (is_array($primary_key)) {
            foreach ($primary_key as $attribute) {
                $unique_attributes[$attribute] = $this->$attribute;
            }
        } else {
            $unique_attributes = [$primary_key => $this->$primary_key];
        }

        // Some attributes can never be saved

        $always_exclude = array_merge($this->exclude ?? [], self::ATTRIBUTES_ALWAYS_HIDDEN);
        $attributes = array_diff_key($this->serialized(true), array_flip($always_exclude));

        // Save the thing

        $this->collection->updateOne(
            $unique_attributes,
            ['$set' => $attributes],
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
