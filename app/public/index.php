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

// Defined in the .htaccess

define('BASE_DIR', getenv('BASE_DIR'));

// Include Composer autoloader

require_once BASE_DIR . '/vendor/autoload.php';

// Include globally-required dependencies

require_once BASE_DIR . '/includes/constants.php';

require_once BASE_DIR . '/engine/runtime.php';
require_once BASE_DIR . '/engine/APIRequest.php';

// Decode the incoming request and execute it

$incoming_request = new APIRequest();
$incoming_request->execute();

?>
