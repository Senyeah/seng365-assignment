<?php

/**
 * Copyright 2016 by Jack Greenhill <jackgreenhill@me.com>
 *
 * This file is part of APIEngine.
 *
 * APIEngine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * APIEngine is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with APIEngine.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @license GPL-3.0+ <http://spdx.org/licenses/GPL-3.0+>
 */

namespace APIEngine;

require_once BASE_DIR . '/includes/api/APIError.php';

class Request {

    public $method;
    public $arguments;
    public $headers;
    public $user;

    /**
	 * Requires a set of parameters to be passed in $_REQUEST, or else an error
	 * is reported to the user.
	 *
	 * Example usage: $request->expect('name', 'password');
	 */
    function expect() {
		foreach (func_get_args() as $argument) {
			if (!isset($_REQUEST[$argument])) {
				throw new \APIError(400, ucfirst($argument) . ' is required');
			}
		}
	}

	/**
	 * Identical to above, except the parameters are only expected if $condition
	 * evaluates to true.
	 */
	function expect_if($condition) {
		if ($condition) {
			foreach (array_slice(func_get_args(), 1) as $argument) {
				if (!isset($_REQUEST[$argument])) {
					throw new \APIError(400, ucfirst($argument) . ' is required');
				}
			}
		}
	}

	/**
	 * Requires any one or more parameters to be present in $_REQUEST, or else an error
	 * is reported to the user.
	 */
	function expect_any() {
		$argument_set = false;

		foreach (func_get_args() as $argument) {
			if (isset($_REQUEST[$argument])) {
				$argument_set = true;
				break;
			}
		}

		if ($argument_set == false) {
			$param_list = '(' . implode(', ', func_get_args()) . ')';
			throw new \APIError(400, "One or more parameters from $param_list expected but not found");
		}
	}

}

class Method {
	const GET = 'GET';
    const POST = 'POST';
    const DELETE = 'DELETE';
    const PUT = 'PUT';
}

interface Requestable {
    public function execute($request);
}

?>
