<?php
require_once 'Interface.php';

/**
 * FormUploader
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Uploader_FileForm implements Uploader_Interface
{

    /**
     * Сохранить
     *
     * @access public
     * @param string $path путь
     * @return boolean
     */
    function save($path)
    {
        if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)) {
            return false;
        }

        return true;
    }


    /**
     * Получить название файла
     *
     * @access public
     * @return string
     */
    function getName()
    {
        return $_FILES['qqfile']['name'];
    }


    /**
     * Получить размер файла
     *
     * @access public
     * @throws Uploader_Exception если не может получить $_SERVER['CONTENT_LENGTH']
     * @return int
     */
    function getSize()
    {
        return $_FILES['qqfile']['size'];
    }
}