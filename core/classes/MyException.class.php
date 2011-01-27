<?php
/**
 * MyException
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */
class Q_MyException extends Exception
{
    public function setLine($line)
    {
        $this->line = $line;
    }
    
    public function setFile($file)
    {
        $this->file = $file;
    }
}