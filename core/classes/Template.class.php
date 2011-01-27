<?php
/**
 * Взаимодействие с шаблонизатором Twig
 * 
 * @author Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2010, qbbr
 */
class Q_Template
{
    /**
     * @staticvar Twig_Environment
     */
    protected static $_tmpl = null;
    
    /**
     * @staticvar string
     */
    protected static $_access = 'client';
    
    /**
     * Шаблонизатор
     *
     * @static
     * @access public
     * @return Twig_Environment
     */
    static public function getTemplate()
    {
        if (is_null(self::$_tmpl)) {
            
            $loader = new Twig_Loader_Filesystem(APPS . DS . self::$_access . DS . 'views' . DS);

            self::$_tmpl = new Twig_Environment($loader, array(
                'debug' => (bool)Q_Registry::get('settings', 'debug'),
                'cache' => TMP . DS . 'templates',
                'auto_reload' => true
            ));
            
            self::$_tmpl->addGlobal('user', Q_Registry::get('user'));
            self::$_tmpl->addGlobal('leftMenu', Q_Configs::get('LeftMenu'));
        }

        return self::$_tmpl;
    }
    
    /**
     * Назначить права доступа (admin или client)
     *
     * @static
     * @access public
     * @param string $access
     * @return void
     */
    public static function setAccess($access)
    {
        self::$_access = $access;
    }
    
    /**
     * Назначить директорию с шаблонами
     *
     * @static
     * @access public
     * @param string|array $path Путь до шаблонов
     * @return void
     */
    public static function addPath($path)
    {
        
        $loader = self::getTemplate()->getLoader();

        $oldPaths = $loader->getPaths();

        if (!is_array($path)) {
            $path = array($path);
        }
        
        $loader->setPaths(array_merge($oldPaths, $path));
    }
    
    /**
     * Добавить глобальную переменную
     *
     * @static
     * @access public
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function addGlobal($key, $value)
    {
        self::$_tmpl->addGlobal($key, $value);
    }
}