<?php
include_once dirname(__FILE__) . DS . 'Uploader' . DS . 'Exception.php';

/**
 * Uploader
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Uploader
{

    /**
     * Допустимые расширения
     * @access private
     * @var array
     */
    private $_allowedExtensions = array();

    /**
     * Максимальный размер файла
     * @access private
     * @var int
     */
    private $_sizeLimit = 0;

    /**
     * File handler
     * @access private
     * @var object
     */
    private $_file;

    /**
     * Название файла
     * @access private
     * @var string
     */
    private $_filename = '';


    /**
     * Constructor
     *
     * @access public
     * @param array $allowedExtensions допустимые расширения
     * @param int $sizeLimit максимальный размер файла
     * @return void
     */
    public function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760)
    {
        $allowedExtensions = array_map('strtolower', $allowedExtensions);

        $this->_allowedExtensions = $allowedExtensions;
        $this->_sizeLimit = $sizeLimit;

        $this->checkServerSettings();

        if (isset($_GET['qqfile'])) {
            include_once dirname(__FILE__) . DS . 'Uploader' . DS . 'Xhr.php';
            $this->_file = new Uploader_Xhr();
        } elseif (isset($_FILES['qqfile'])) {
            include_once dirname(__FILE__) . DS . 'Uploader' . DS . 'FileForm.php';
            $this->_file = new Uploader_FileForm();
        } else {
            $this->_file = false;
        }
    }


    /**
     * Проверка директив сервера
     *
     * @access private
     * @return mixed
     */
    private function checkServerSettings()
    {
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->_sizeLimit || $uploadSize < $this->_sizeLimit) {
            $size = max(1, $this->_sizeLimit / 1024 / 1024) . 'M';
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        }

        return true;
    }


    /**
     * Перевести в байты
     *
     * @access private
     * @param string $str
     * @return int
     */
    private function toBytes($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);

        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }

        return $val;
    }


    /**
     * Загрузить
     *
     * @access public
     * @param string $uploadDirectory директория, в которую будут загружаться файлы
     * @param boolean $replaceOldFile
     * @return array array('success'=>true) or array('error'=>'error message')
     */
    public function upload($uploadDirectory, $extraParams = array())
    {
        if (!is_writable($uploadDirectory)) {
            return $this->error('Server error. Upload directory isn\'t writable');
        }

        if (!$this->_file) {
            return $this->error('No files were uploaded');
        }

        $size = $this->_file->getSize();

        if ($size == 0) {
            return $this->error('File is empty');
        }

        if ($size > $this->_sizeLimit) {
            return $this->error('File is too large');
        }

        $pathinfo = pathinfo($this->_file->getName());
        $filename = $pathinfo['filename'];
        $ext = strtolower($pathinfo['extension']);
        $this->_filename = $filename = md5(uniqid() . microtime()) . '.' . $ext;

        if ($this->_allowedExtensions && !in_array(strtolower($ext), $this->_allowedExtensions)) {
            $these = implode(', ', $this->_allowedExtensions);
            return $this->error('File has an invalid extension, it should be one of '. $these);
        }

        if ($this->_file->save($uploadDirectory . $filename)) {
            $return = array(
                'success' => true,
                'pathinfo' => pathinfo($filename)
            );

            if (!empty($extraParams)) {
                $return = array_merge($return, $extraParams);
            }

            return $return;
        } else {
            return $this->error('Could not save uploaded file. The upload was cancelled, or server error encountered');
        }

    }


    /**
     * Получить название файла
     *
     * @access public
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }


    /**
     * Ошибка
     *
     * @access private
     * @param string $msg сообщение
     * @return array
     */
    private function error($msg)
    {
        return array(
            'error' => $msg
        );
    }

}