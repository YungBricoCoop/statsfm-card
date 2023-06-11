<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Intervention\Image\ImageManagerStatic as Image;

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
		$rect .= '<defs><linearGradient id="' . $id . '" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:' . $gradientStart . ';stop-opacity:1" /><stop offset="100%" style="stop-color:' . $gradientStop . ';stop-opacity:1" /></linearGradient></defs>';
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

function addText($text, $x, $y, $width, $color, $size, $anchor)
{
	return '<text x="' . $x . '" y="' . $y . '" width="' . $width . '" fill="' . $color . '" style="text-anchor: ' . $anchor . '; font-family: Arial; font-size: ' . $size . 'px;">' . $text . '</text>';
}
