<?php
/**
 * adminController
 *
 * @author Sokolov Innokenty, <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_AdminController extends Q_Controller
{

    protected $_access = 'admin';

    public function __construct($moduleName)
    {
        $this->_moduleName = $moduleName;
        $this->_moduleDir = APPS . DS . $this->_access;
        $this->_viewsDir = $this->_moduleDir . DS . 'views';
    }

}