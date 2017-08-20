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

require_once 'AcceptHeader.php';

/**
 * Represents an exception which occurs due to user action, for example
 * having bad request parameters or by not being authorised.
 */
class APIError extends Exception {

	const ERROR_KEY = 'error';
	const ERROR_CODE_KEY = 'code';
	const ERROR_DETAIL_KEY = 'message';

    protected $code;
    protected $message;

    function __construct($code, $message = null) {
		$this->code = $code;
		$this->message = $message;
    }

}

/**
 * Sets the exception handler for all errors thrown. Dumps a JSON-encoded
 * message and sets the header, if possible.
 */
set_exception_handler(function($exception) {

	// Give the requester a JSON-encoded message if at all possible

	$readable_message = $exception->getMessage();

	// Append the file name and line number to the error message for debugging
	// if it's not an APIError

	if ($exception instanceof APIError == false && $readable_message != null) {
		$readable_message .= ' in file ' . $exception->getFile() . ' on line ' . $exception->getLine();
	}

	if (headers_sent() || php_sapi_name() == 'cli') {

		echo 'Exception: ' . htmlentities($readable_message);

	} else {

		// If it's an APIError, get the code. Otherwise it's just a 500

	    $code = $exception instanceof APIError ? $exception->getCode() : 500;

		// Parse the accept encodings sent by the user agent

	    $headers = new AcceptHeader($_SERVER['HTTP_ACCEPT']);

	    // Get all of the encodings the user agent supports

	    $accept_encodings = array_map(function($item) {
		    return $item['raw'];
		}, $headers->getArrayCopy());

	    // Generate a human readable format if that's what they are requesting

	    $human_readable = in_array('application/json', $accept_encodings) == false &&
	                      in_array('text/html', $accept_encodings);

		$mask = $human_readable ? JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT : 0;

		// Set the response code and content type

	    http_response_code($code);
	    header('Content-Type: application/json');

		// If it's an exception because they're not authorised, let them know how to authorise

	    if ($code == 401) {
		    header('WWW-Authenticate: Token realm=\'Crowdfunding API\'');
	    }

	    $response[APIError::ERROR_KEY] = [APIError::ERROR_CODE_KEY => $code];

	    if ($readable_message != null) {
		    $response[APIError::ERROR_KEY][APIError::ERROR_DETAIL_KEY] = $readable_message;
	    }

	    // Actually deliver the message to them now

		echo json_encode($response, $mask);

	}

 });

?>
