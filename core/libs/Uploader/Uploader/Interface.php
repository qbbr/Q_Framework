<?php
/**
 * Interface
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

interface Uploader_Interface
{

    public function save($path);

    public function getName();

    public function getSize();

}