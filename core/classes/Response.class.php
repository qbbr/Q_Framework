<?php
/**
 * Ответ
 * 
 * @example
 * <code>
 * return new Q_Response(
 *     'shop.html',
 *     array(
 *         'param1' => 'xD1',
 *         'param2' => 'xD2'
 *     )
 * );
 * </code>
 * 
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */
class Q_Response
{
    protected $_name = '';
    protected $_context = array();

    public function __construct($name, array $context = array())
    {
        $this->_name = $name;
        $this->_context = $context;
    }

    public function __toString()
    {
        $template = Q_Template::getTemplate()->loadTemplate($this->_name);
        return $template->render($this->_context);
    }
}