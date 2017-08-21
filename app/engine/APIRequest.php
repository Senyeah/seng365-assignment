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

require_once BASE_DIR . '/includes/helpers/RequiresAuthenticationTrait.php';
require_once BASE_DIR . '/models/Model.php';
require_once BASE_DIR . '/models/User.php';
require_once BASE_DIR . '/models/AccessToken.php';

use APIEngine\Method;

class APIRequest {

	private $method;
	private $arguments;
	private $headers;

	private $redirect_tree;
	private $uploaded_files;

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
        $request->headers = $this->headers;
        $request->arguments = [];

        // Attempt to extract their access token from the provided headers

        $provided_token = $request->headers['X-Authorization'] ?? null;

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
	public function execute() {

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

            // Check to see if we must be authenticated

            if (in_array('RequiresAuthentication', class_uses($desired_entry->class_name))
                && is_null($request->user)) {

                throw new APIError(401, 'Unauthorized');
            }

	        $returned = $instance->execute($request);

	        // If a model was returned, cast it as a JSON response

	        if ($returned instanceof Model || is_array($returned)) {
                header('Content-Type: application/json');
                echo json_encode($returned instanceof Model ? $returned->serialized() : $returned);
	        }

        } else {
	        throw new APIError(500, 'Class \'' . $desired_entry->class_name . '\' does not implement interface Requestable');
        }

	}

	/**
	 * Gets the redirect tree from the cache or from memory, if the cache does not have it stored.
	 */
	private function get_redirect_tree() {

		if (!file_exists(REDIRECT_TREE_PATH)) {
			throw new APIError(500, 'The endpoint definition file does not exist');
		}

		$redirect_tree_string = file_get_contents(REDIRECT_TREE_PATH);
		return json_decode($redirect_tree_string, true);

	}

	/**
     * Parses a file and its header form a multipart/form-data upload request
     */
	private function parse_multipart_header($data, $boundary) {

        $headers = substr($data, 0, $boundary);

        // Parse the headers

        preg_match('/Content-Disposition: (.*)\r\nContent-Type: (.*)/i', $headers, $parsed_meta_headers);
        preg_match('/name="(.*)";.*filename="(.*)".*/', $parsed_meta_headers[1], $parsed_headers);

        return [
            'name' => $parsed_headers[1],
            'filename' => $parsed_headers[2],
            'mime' => $parsed_meta_headers[2]
        ];

	}

	/**
     * Handles a file upload by reading information from the php://input stream.
     * Adds it to the $_FILES array, the same as a POST request would.
     */
	private function handle_put_file_upload() {

    	preg_match('/boundary=(.*)/', $_SERVER['CONTENT_TYPE'], $boundary);

    	$data = file_get_contents('php://input');
        $file_components = explode('--' . $boundary[1], $data);

        foreach ($file_components as $file_data) {

            $file_header_boundary = strpos($file_data, "\r\n\r\n");

            if ($file_header_boundary === false) {
                continue;
            }

            $file = substr($file_data, $file_header_boundary + 4, -2);
            $headers = $this->parse_multipart_header($file_data, $file_header_boundary);

            $tmp_filename = '/tmp/php' . bin2hex(openssl_random_pseudo_bytes(3));
            file_put_contents($tmp_filename, $file);

            $this->uploaded_files[] = $tmp_filename;

            $_FILES[$headers['name']] = [
                'name' => $headers['filename'],
                'type' => $headers['mime'],
                'tmp_name' => $tmp_filename,
                'size' => strlen($file),
                'error' => UPLOAD_ERR_OK
            ];

        }

	}

    /**
     * Constructs the request
     */
	function __construct() {

    	// Add request headers

    	$this->headers = apache_request_headers();

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

        // Are they uploading a file via PUT?
        // PHP doesn't add it to the $_FILES array unless it's a POST

        if ($this->method == Method::PUT && strpos($_SERVER['CONTENT_TYPE'], 'boundary') !== false) {
            $this->uploaded_files = [];
            $this->handle_put_file_upload();
        }

        // Only include nonempty arguments

        $this->arguments = array_filter(explode('/', $_REQUEST['arguments']), function($value) {
	        return $value !== '';
	    });

	    // Leaving them there can screw with validation

	    unset($_REQUEST['arguments']);

	}

	/**
     * Deletes any uploaded files in the /tmp directory
     */
	function __destruct() {
        foreach ($this->uploaded_files ?? [] as $file) {
            unlink($file);
        }
	}

}

?>
