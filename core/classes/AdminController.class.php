<?php
/**
 * adminController
 *
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_AdminController extends Q_Controller
{

    protected $_access = 'admin';

    public function __construct($moduleName)
    {
        $this->_moduleName = $moduleName;
    }
    
    protected function log()
    {
        
        
        dir();
        $log = new Logs();
        $log->user_id = Q_Registry::get('user', 'id');
        $log->module = $this->_moduleName;
    }

}