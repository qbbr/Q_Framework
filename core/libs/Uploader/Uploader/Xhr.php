<?php
require_once 'Interface.php';

/**
 * XhrUploader
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Uploader_Xhr implements Uploader_Interface
{
    /**
     * Сохранить
     *
     * @access public
     * @param string $path путь
     * @return boolean
     */
    public function save($path)
    {
        $input = fopen('php://input', 'r');
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize()){
            return false;
        }

        $target = fopen($path, 'w');
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    /**
     * Получить название файла
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $_GET['qqfile'];
    }


    /**
     * Получить размер файла
     *
     * @access public
     * @throws Uploader_Exception если не может получить $_SERVER['CONTENT_LENGTH']
     * @return int
     */
    public function getSize()
    {
        if (isset($_SERVER['CONTENT_LENGTH'])){
            return (int)$_SERVER['CONTENT_LENGTH'];
        } else {
            throw new Uploader_Exception('Не могу получить размер файла');
        }
    }
}