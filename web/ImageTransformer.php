<?php
/**
 * Магическая обработка картинок на лету
 *
 * @uses
 * add to htacces
 * RewriteRule ^(.*)_{4}(\d{1,4})?x(\d{1,4})?(s|i|si|is)?\.(jpe?g|png|gif)$ ImageTransformer.php?file=$1&ext=$5&width=$2&height=$3&params=$4 [L]
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

error_reporting(-1);
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'constants.php';

$url =  trim($_SERVER['REDIRECT_URL'], DS);

$file = $_GET['file'];
$ext = $_GET['ext'];
$maxWidth = !empty($_GET['width']) ? $_GET['width'] : null;
$maxHeight = !empty($_GET['height']) ? $_GET['height'] : null;
$params = $_GET['params'];

$strict = is_numeric(strpos($params, 's'))
        ? true
        : false;

$inner = is_numeric(strpos($params, 'i'))
        ? true
        : false;

if (empty($file) || empty($ext)
   || (empty($maxWidth) && empty($maxHeight))) {
    error();
}

// путь до оригинального файла
$file = WEB . DS . str_replace(array('\\', '/'), DS, $file) . '.' . $ext;

// проверяем существует ли такой файл
if (!is_file($file)) {
    error();
}

// убедимся что $x и $y это числа и они больше 0
if (
    ((is_numeric($maxWidth) && $maxWidth > 0) || is_null($maxWidth))
    &&
    ((is_numeric($maxHeight) && $maxHeight > 0) || is_null($maxHeight))
    ) {

    $properties = getimagesize($file);
    $width = $properties[0];
	$height = $properties[1];
	$mimeType = $properties['mime'];

    header('Content-type: ' . $mimeType);
    
    $cacheFile = CACHE . DS . md5(basename($url)) . '.' . $ext;

    // если файл есть в кэше, то выводим его
    if (is_file($cacheFile)) {
        readfile($cacheFile);
        exit();
    } else {
        $no_work = false;
        if ($maxWidth && $maxHeight) {
            if ($width > $maxWidth || $height > $maxHeight) {
                if ($strict) {
                    $newWidth = $maxWidth;
                    $newHeight = $maxHeight;
                } else {
                    if (($width > $maxWidth && $width > $height && $maxWidth < $maxHeight) || ($inner && $width > $height)) {
                        $p = ($maxWidth * 100) / $width;
                        $newWidth = $maxWidth;
                        $newHeight = ($height * $p) / 100;
                    } else {
                        $p = ($maxHeight * 100) / $height;
                        $newHeight = $maxHeight;
                        $newWidth = ($width * $p) / 100;
                    }

                }
            } else {
                $no_work = true;
            }
        } else if ($maxWidth) {
            if ($width > $maxWidth) {
                $p = ($maxWidth * 100) / $width;
                $newWidth = $maxWidth;
                $newHeight = ($height * $p) / 100;
            } else {
                $no_work = true;
            }
        } else if ($maxHeight) {
            if ($height > $maxHeight) {
                $p = ($maxHeight * 100) / $height;
                $newHeight = $maxHeight;
                $newWidth = ($width * $p) / 100;
            } else {
                $no_work = true;
            }
        } else {
            $no_work = true;
        }

        if ($no_work) {
            readfile($file);
            exit();
        }

        $imageP = imagecreatetruecolor($newWidth, $newHeight);

        if ($mimeType == 'image/jpeg') {
            $image = imagecreatefromjpeg($file);
        } else if ($mimeType == 'image/png') {
            $image = imagecreatefrompng($file);
        } else if ($mimeType == 'image/gif') {
            $image = imagecreatefromgif($file);
        }

        imagecopyresampled($imageP, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        if ($mimeType == 'image/jpeg') {
            imagejpeg($imageP, null, 100);
        } else if ($mimeType == 'image/png') {
            imagepng($imageP);
        } else if ($mimeType == 'image/gif') {
            imagegif($imageP);
        }

        $contents = ob_get_contents();

        /* cache --- {{{ */
        if (is_dir(CACHE) && is_writable(CACHE)) {
            $f = fopen($cacheFile, 'w');
            fwrite($f, $contents);
            fclose($f);
        }
        /* }}} --- */

        imagedestroy($imageP);
        imagedestroy($image);
    }
}

/**
 * Ошибка
 */
function error()
{
    die();
}