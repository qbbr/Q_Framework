<?php
/**
 * CSS and Javascript Combinator
 *
 * @uses
 * add to htaccess
 * RewriteRule ^_\/(admin|client)/([^\//]+)\.css$ qcc.php?a=$1&m=$2&t=css [L]
 *
 * @license MIT
 * @author Niels Leenheer, Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

error_reporting(-1);
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'constants.php';

//header('Content-type: text/css');

if (!isset($_GET['a'], $_GET['m'], $_GET['t'])) exit();

$compress = isset($_GET['c']);
$module = $_GET['m'];
$type = $_GET['t'];

if ($type != 'css' && $type != 'js') exit();

switch ($_GET['a']) {
    case 'client':
        $p = CLIENT_CONTROLLERS . DS . $module . DS . 'etc' . DS . 'css';
        break;
    
    case 'admin':
        $p = ADMIN_CONTROLLERS . DS . $module . DS . 'etc' . DS . 'css';
        break;

    default:
        exit();
}

$cache = true;
$cachedir = CACHE;

$elements = array();
$css = '';
$lastmodified = 0;
if (is_dir($p)) {
    foreach (glob($p . DS . '*.' . $type) as $filename) {
        array_push($elements, $filename);
        $lastmodified = max($lastmodified, filemtime($filename));
    }
}


// Send Etag hash
$hash = $lastmodified . '-' . md5($p . $module . $type . $compress);

header('Etag: "' . $hash . '"');

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
    stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"') {
    header ('HTTP/1.0 304 Not Modified');
    header ('Content-Length: 0');
} else {
    // First time visit or files were modified
    if ($cache) {
        // Determine supported compression method
        $gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
        $deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

        // Determine used compression method
        $encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

        // Check for buggy versions of Internet Explorer
        if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera')
            && preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
            $version = floatval($matches[1]);

            if ($version < 6) $encoding = 'none';

            if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) $encoding = 'none';
        }

        // Try the cache first to see if the combined files were already generated
        $cachefile = 'cache-' . $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');
   

        if (file_exists($cachedir . '/' . $cachefile)) {
            if ($fp = fopen($cachedir . '/' . $cachefile, 'rb')) {

                if ($encoding != 'none') {
                    header ('Content-Encoding: ' . $encoding);
                }

                header ('Content-Type: text/' . $type);
                header ('Content-Length: ' . filesize($cachedir . '/' . $cachefile));

                fpassthru($fp);
                fclose($fp);
                exit;
            }
        }
    }
    
    function packCss($file) {
        $file = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $file);
        $file = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '   ', '    '), '', $file);
        $file = str_replace(': ', ':', $file);
        $file = str_replace('; ', ';', $file);
        $file = str_replace(', ', ',', $file);
        $file = str_replace(' {', '{', $file);
        $file = str_replace('{ ', '{', $file);
        $file = str_replace(' }', '}', $file);
        $file = str_replace('} ', '}', $file);
        return $file;
    }

    // Get contents of the files
    $contents = '';
    //reset($elements);
    for ($i = 0; $i < count($elements); $i++) {
        if ($i != 0) $contents .= "\n\n";
        $content = file_get_contents($elements[$i]);
        if ($compress && $type == 'css') $content = packCss($content);
        $contents .= "/**\n * File: " . basename($elements[$i]) ."\n */\n" . $content;
    }
   

    // Send Content-Type
    header ('Content-Type: text/' . $type);

    if (isset($encoding) && $encoding != 'none') {
        // Send compressed contents
        $contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
        header ('Content-Encoding: ' . $encoding);
        header ('Content-Length: ' . strlen($contents));
        echo $contents;
    } else {
        // Send regular contents
        header ('Content-Length: ' . strlen($contents));
        echo $contents;
    }

    // Store cache
    if ($cache) {
        if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
            fwrite($fp, $contents);
            fclose($fp);
        }
    }
}