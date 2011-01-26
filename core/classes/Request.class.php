<?php
/**
 * Request - хранит в себе параметры запроса
 * 
 * У всех *Action методов в Q_Controller`ах
 * первым аргументом должен быть `Q_Request $request`
 * или его экземпляр
 * 
 * @example
 * <code>
 * class Q_MyController extends Q_AdminController
 * {
 * 
 *     public function indexAction(Q_Request $request)
 *     {
 *          echo $request['id'];
 *          // аналогично
 *          echo $request->id;
 *     }
 * 
 * }
 * </code>
 * 
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */

class Q_Request implements ArrayAccess
{

    /**
     * хранилище
     * @var array
     */
    protected $_container = array();


    /**
     * Конструктор
     * для множественного назначения
     *
     * @param array $array миссив с данными
     */
    public function __construct(array $array)
    {
        foreach ($array as $key => $value) {
            $this[$key] = $value;
        }
    }


    /**
     * Проверка на существование
     * 
     * @access public
     * @param string|integer $key
     * @return boolean 
     */
    public function offsetExists($key)
    {
        return isset($this->_container[$key]);
    }


    /**
     * Getter
     *
     * @access public
     * @param string|integer $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return isset($this->_container[$key]) ? $this->_container[$key] : null;
    }


    /**
     * Setter
     *
     * @access public
     * @param string|integer $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->_container[] = $value;
        } else {
            $this->_container[$key] = $value;
        }
    }


    /**
     * Запрет на удаление из хранилища
     *
     * @access public
     * @param type $key
     * @return false
     */
    public function offsetUnset($key) {
        return false;
    }


    /**
     * Getter
     * при обращение к атрибутам класса (->)
     *
     * @access public
     * @param string|integer $key
     * @return mixed
     */
    public function __get($key) {
        return $this->offsetGet($key);
    }

}