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

	if (isset($array[$key]) && is_array($array[$key]) == false) {
		return in_array($property, array_keys($array));
	}

	if ($key == '*') {
		return is_array($array) && count($array) > 0 &&
			   array_reduce(
				   $array,
				   function ($carry, $sequential_item) use ($new_components) {
					   return $carry && array_has($sequential_item, $new_components);
				   },
				   true
			   );
	}

	return in_array($key, array_keys($array)) && array_has($array[$key], $new_components);

}

?>
