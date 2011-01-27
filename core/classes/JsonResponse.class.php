<?php
/**
 * Ответ в формате JSON
 * 
 * @example
 * <code>
 * return new Q_JsonResponse(
 *     array(
 *         'success' => false, // optional, default = true
 *         'result' => 'data',
 *         'errors' => array(1, 2, 3) // optional, default = array()
 *     )
 * );
 * </code>
 * 
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */
class Q_JsonResponse
{
    protected $_params = array();

    public function __construct(array $params = array())
    {
        if (!isset($params['success'])) $params['success'] = true;
        if (!isset($params['error'])) $params['error'] = '';
        
        $this->_params = $params;
    }

    public function __toString()
    {
        return json_encode($this->_params);
    }
}