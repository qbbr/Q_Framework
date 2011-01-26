<?php
/**
 * CSS для модулей
 *
 * @uses
 * add to htaccess
 * RewriteRule ^@\/(admin|client)(.*\.css)$ getCss.php?a=$1&p=$2
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

error_reporting(-1);
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'constants.php';

header('Content-type: text/css');

if (!isset($_GET['a'], $_GET['p'])) exit();

$path = str_replace(array('\\', '/'), DS, $_GET['p']);

switch ($_GET['a']) {
    case 'client':
        $p = CLIENT_CONTROLLERS . $path;
        break;
    
    case 'admin':
        $p = ADMIN_CONTROLLERS . $path;
        break;

    default:
        exit();
}

if (is_file($p)) {
    echo file_get_contents($p);
}