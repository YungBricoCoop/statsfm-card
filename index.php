<?php

require 'vendor/autoload.php';
require 'constants.php';

use GuzzleHttp\Client;
use Intervention\Image\ImageManagerStatic as Image;

$client = new Client([
	'headers' => [
		'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
		'Accept-Language' => 'en-US,en;q=0.9',
		'Accept' => '*/*',
		'Accept-Encoding' => 'gzip, deflate',
	],
	'verify' => false,
]);

$PARAMS = DEFAULT_PARAMS;
$CACHE_KEY = '';


function handleRequest()
{
	//header('Content-Type: image/svg+xml');
	$GLOBALS['CACHE_KEY'] = getUrlHash();
	$cache = getFromCache();
	if ($cache) {
		echo $cache;
		return;
	}
	// parse query params and change default values
	foreach ($GLOBALS['PARAMS'] as $key => $value) {
		if (isset($_GET[$key])) {
			$GLOBALS['PARAMS'][$key] = $_GET[$key];
		}
	}

	echo createSvg();
}

function getRandomId()
{
	return substr(md5(rand()), 0, 12);
}

function addRect($x, $y, $width, $height, $color, $radius, $gradientStart, $gradientStop)
{
	$rect = '';
	$color = 'fill="' . $color . '"';
	if ($gradientStart) {
		$id = getRandomId();
		$rect .= '<defs><linearGradient id="' . $id . '" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#' . $gradientStart . ';stop-opacity:1" /><stop offset="100%" style="stop-color:#' . $gradientStop . ';stop-opacity:1" /></linearGradient></defs>';
		$color = 'fill="url(#' . $id . ')"';
	}

	$rect .= '<rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . $height . '" ' . $color . ' rx="' . $radius . '" ry="' . $radius . '" />';
	return $rect;
}

function addImg($client, $url, $x, $y, $width, $height, $radius)
{
	$response = $client->get($url);
	$image_data = $response->getBody();

	$img = Image::make($image_data);

	$aspect_ratio = $img->width() / $img->height();

	if ($aspect_ratio != 1) {
		$smaller_side = $img->width() < $img->height() ? $img->width() : $img->height();

		if ($img->width() < $img->height()) {
			$img->resize($smaller_side, null, function ($constraint) {
				$constraint->aspectRatio();
			});
		} else {
			$img->resize(null, $smaller_side, function ($constraint) {
				$constraint->aspectRatio();
			});
		}
		$img->crop($smaller_side, $smaller_side);
	}

	$image_base64 = base64_encode($img->encode('png'));

	if ($radius) {
		$id = getRandomId();
		$mask = '<defs><mask id="' . $id . '"><rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . $height . '" fill="white" rx="' . $radius . '" ry="' . $radius . '" /></mask></defs>';
		return $mask . '<image x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . $height . '" href="data:image/png;base64,' . $image_base64 . '" mask="url(#' . $id . ')" />';
	}

	return '<image x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . $height . '" href="data:image/png;base64,' . $image_base64 . '" />';
}

function addText($text, $x, $y, $width, $color, $size, $weight, $anchor)
{
	return '<text x="' . $x . '" y="' . $y . '" width="' . $width . '" fill="' . $color . '" style="text-anchor: ' . $anchor . '; font-family: Arial; font-size: ' . $size . 'px; font-weight: ' . $weight . ';">' . $text . '</text>';
}

function createSvg()
{
	$client = $GLOBALS['client'];
	$params = $GLOBALS['PARAMS'];

	$username = $params['username'];
	$type = $params['type'];
	$range = $params['range'];
	$display = $params['display'];
	$limit = $params['limit'];

	$url = "https://beta-api.stats.fm/api/v1/users/$username/top/$type?range=$range&limit=$limit";

	$response = $client->get($url);
	$top_elements = json_decode($response->getBody(), true)['items'];

	$image_size = 80;
	$start_x = ($params['width'] - ($image_size * $params['limit'] + $params['spacing'] * ($params['limit'] - 1))) / 2;
	$start_y = ($params['height'] - $image_size) / 2;

	$svg_content = addRect(0, 0, $params['width'], $params['height'], '', $params['rounded'], $params['g_start'], $params['g_stop']);
	$index = 0;
	foreach ($top_elements as $i => $top) {
		if ($index == $params['limit']) break;

		$local_y_offset = $i % 2 * $params['y_offset'];
		$local_start_y = $start_y - $local_y_offset;
		$local_start_x = $start_x + ($image_size + $params['spacing']) * $i;
		$local_artist_text_y = $local_start_y - 5;
		$local_h_text_y = $local_start_y + $image_size + 12;
		$x_center = $start_x + ($image_size + $params['spacing']) * $i + $image_size / 2;

		$name = '';
		$data = 0;

		if ($type == 'artists') {
			$name = $top['artist']['name'];
			$image_url = $top['artist']['image'] ?? NOT_FOUND_IMAGE;
		} else if ($type == 'albums') {
			$name = $top['album']['name'];
			$image_url = $top['album']['image'] ?? NOT_FOUND_IMAGE;
		} else if ($type == 'tracks') {
			$name = $top['track']['albums'][0]['name'];
			$image_url = $top['track']['albums'][0]['image'] ?? NOT_FOUND_IMAGE;
		}

		if (isset($top['playedMs']) && $display == 'hours') {
			$data = $top['playedMs'];
			$data = round($data / 1000 / 60 / 60);
			$data = number_format($data, 0, '.', ' ');
			$data .= ' h';
		} else if (isset($top['streams']) && $display == 'streams') {
			$data = $top['streams'];
			$data = number_format($data, 0, '.', ' ');
			$data .= ' s';
		}

		$svg_content .= addImg($client, $image_url, $local_start_x, $local_start_y, $image_size, $image_size, $params['i_rounded']);
		$svg_content .= addText($name, $x_center, $local_artist_text_y, $image_size, "white", 9, 'normal', 'middle');
		if ($data) {
			$svg_content .= addText($data, $x_center, $local_h_text_y, $image_size, "white", 9, 'bold', 'middle');
		}
		$index++;
	}

	$svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="' . $params['width'] . '" height="' . $params['height'] . '">' . $svg_content . '</svg>';

	// save image in cache
	$cache_file = 'cache/' . $GLOBALS['CACHE_KEY'] . '.svg';
	file_put_contents($cache_file, $svg);

	return $svg;
}

function getUrlHash()
{
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$hash = md5($url);
	return $hash;
}

function getFromCache()
{
	$file = basename($GLOBALS['CACHE_KEY']) . '.svg';
	$cache_file = CACHE_FOLDER . '/' . $file;

	// check if image in cache and is not older than the cache time
	if (file_exists($cache_file) && (time() - filemtime($cache_file) < CACHE_TIME)) {
		return file_get_contents($cache_file);
	}
	return false;
}

function saveToCache($svg)
{
	// create cache directory if it doesn't exist
	if (!is_dir(CACHE_FOLDER)) {
		mkdir(CACHE_FOLDER, 0755, true);
	}

	// prevent directory traversal attacks even if normally this shouldn't be possible
	$file = basename($GLOBALS['CACHE_KEY']) . '.svg';
	$cache_file = CACHE_FOLDER . '/' . $file;

	// prevent writing outside of cache directory
	$real_path = realpath(CACHE_FOLDER);
	if (strpos($cache_file, $real_path) === 0) {
		file_put_contents($cache_file, $svg);
	}
}


handleRequest();
