<?php

/**
 * array_has: Checks that a given item exists in an array using
 * dot notation:
 *
 * array_has(['user' => ['id' => 2]], 'user.id')
 * true
 *
 * array_has(['locations' => [['name' => 'chc'], ['name' => 'nsn']]], 'locations.*.name')
 * true
 */
function array_has($array, $property) {

	$components = explode('.', $property);

	$key = array_shift($components);
	$new_components = implode('.', $components);

	if (empty($property)) {

		return true;

	} else if (isset($array[$key]) && is_array($array[$key]) == false) {

		return in_array($property, array_keys($array));

	} else if ($key == '*') {

    	$test_each_item = function ($carry, $item) use ($new_components) {
            return $carry && array_has($item, $new_components);
        };

		return array_reduce($array, $test_each_item, is_array($array) && count($array) > 0);

	} else {

		return in_array($key, array_keys($array)) && array_has($array[$key], $new_components);

	}


}

?>
