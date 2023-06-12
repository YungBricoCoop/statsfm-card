<?php

require 'constants.php';

function clean_cache($directory, $age)
{
	$files = glob(rtrim($directory, '/') . '/*.svg');

	$now = time();

	foreach ($files as $file) {
		if (is_file($file) && $now - filemtime($file) >= $age) {
			unlink($file);
		}
	}
}

clean_cache(CACHE_FOLDER, MAX_CACHE_TIME);
