<?php

define('CACHE_FOLDER', 'cache');
define('CACHE_TIME', 86400); // 1 day
define('MAX_CACHE_TIME', 604800); // 1 week
define('DEFAULT_PARAMS', [
	'username' => '',
	'range' => 'lifetime',
	'type' => 'artists',
	'display' => 'hours',
	'limit' => 5,
	'width' => 580,
	'height' => 180,
	'spacing' => 20,
	'y_offset' => 12,
	'rounded' => 10,
	'i_rounded' => 4,
	'g_start' => '0D1117',
	'g_stop' => '000000',
]);
DEFINE('NOT_FOUND_IMAGE', 'https://upload.wikimedia.org/wikipedia/commons/4/49/A_black_image.jpg');