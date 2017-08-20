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

require_once 'RedirectEntry.php';

require_once BASE_DIR . '/models/Model.php';
require_once BASE_DIR . '/models/User.php';
require_once BASE_DIR . '/models/AccessToken.php';

use APIEngine\Method;

class APIRequest {

	private $method;
	private $arguments;

	private $redirect_tree;

	/**
	 * This traverses the given redirect tree for a given request method and components.
	 *
	 * It returns a RedirectEntry object, which represents the file and components for the
	 * request the user provided.
	 */
	private function redirect_entry_for_request($method, $components) {

		if (is_null($this->redirect_tree) || !array_key_exists($method, $this->redirect_tree)) {
			return null;
		}

		$sub_tree = $this->redirect_tree[$method];
		$current_item = 0;

		while ($current_item < count($components)) {
			$current_component = $components[$current_item];

			if (array_key_exists($current_component, $sub_tree)) {
				$sub_tree = $sub_tree[$current_component];
				$current_item++;
			} else if (array_key_exists(EndpointComponent::WILDCARD, $sub_tree)) {
				$sub_tree = $sub_tree[EndpointComponent::WILDCARD];
				$current_item++;
			} else if (count($current_component) == 0) {
				break;
			} else {
				return null;
			}
		}

		if (array_key_exists(EndpointComponent::ROOT, $sub_tree)) {
			return new RedirectEntry($sub_tree[EndpointComponent::ROOT]);
		} else {
			return null;
		}

	}

	/**
     * Returns an executable request for a given redirect entry
     */
	private function get_request_object($redirect_entry) {

		$request = new APIEngine\Request();

        $request->method = $this->method;
        $request->headers = apache_request_headers();
        $request->arguments = [];

        // Attempt to extract their access token from the provided headers

        $provided_token = $request->headers['X-Authorization'];

        if (!is_null($provided_token)) {
            $request->access_token = AccessToken::from_existing_token($provided_token);
	        $request->user = User::from_id($request->access_token->id);
        } else {
	        $request->user = null;
	        $request->access_token = null;
        }

        // Get the arguments and map them to their names

        foreach ($redirect_entry->parameters as $index => $name) {
	        $request->arguments[$name] = $this->arguments[$index];
        }

        return $request;

	}

    /**
     * Invokes the class specified in the redirect entry
     */
	function execute() {

		// Firstly, find the corresponding redirect entry and make sure there's something
		// to actually execute

		$desired_entry = $this->redirect_entry_for_request($this->method, $this->arguments);

        if (is_null($desired_entry)) {
	        throw new APIError(404, 'The requested endpoint \'/' . $_REQUEST['arguments'] . '\' does not exist');
        }

        // We can now construct the request object and map provided arguments into
        // its array

        $request = $this->get_request_object($desired_entry);

        // Now we open the desired class and ensure that it implements the Requestable interface

        $script_location = BASE_DIR . '/' . trim($desired_entry->file_name, '/');

        // Go to the location of the script and include it here

        chdir(dirname($script_location));
        require_once basename($desired_entry->file_name);

        // At this point the class should exist as it's been included

        if (class_exists($desired_entry->class_name) == false) {
	        throw new APIError(500, 'Class \'' . $desired_entry->class_name . '\' does not exist');
        }

        $instance = new $desired_entry->class_name;

        // We can't execute it if they haven't marked it as requestable

        if ($instance instanceof APIEngine\Requestable) {

            //Check to see if we must be authenticated

            if ($instance->requires_authentication === true && is_null($request->user)) {
                throw new APIError(401, 'Unauthorized');
            }

	        $returned = $instance->execute($request);

	        // If a model was returned, cast it as a JSON response

	        if ($returned instanceof Model) {
                header('Content-Type: application/json');
                echo json_encode($returned->serialized());
	        }

        } else {
	        throw new APIError(500, 'Class \'' . $desired_entry->class_name . '\' does not implement interface Requestable');
        }

	}

	/**
	 * Gets the redirect tree from the cache or from memory, if the cache does not have it stored.
	 */
	function get_redirect_tree() {

		if (!file_exists(REDIRECT_TREE_PATH)) {
			throw new APIError(500, 'The endpoint definition file does not exist');
		}

		$redirect_tree_string = file_get_contents(REDIRECT_TREE_PATH);
		return json_decode($redirect_tree_string, true);

	}

    /**
     * Constructs the request
     */
	function __construct() {

      	// Determine their request method

		$this->method = $_SERVER['REQUEST_METHOD'];

		if (!in_array($this->method, [Method::GET, Method::POST, Method::PUT, Method::DELETE])) {
			throw new APIError(500, 'This server can only accept GET, POST, PUT and DELETE requests');
		}

		// Load the redirect tree into memory

		$this->redirect_tree = $this->get_redirect_tree();

		// Deserialize JSON-encoded requests

        if (in_array($this->method, [Method::POST, Method::PUT, Method::DELETE])) {
            $_REQUEST = array_merge($_REQUEST, json_decode(file_get_contents('php://input'), true) ?? []);
        }

        // Only include nonempty arguments

        $this->arguments = array_filter(explode('/', $_REQUEST['arguments']), function($value) {
	        return $value !== '';
	    });

	}

}

?>
