<?php
require_once dirname(__FILE__) . DS . 'Annotations' . DS . 'AnnotationParser.php';

/**
 * Addendum in zend code style
 * 
 * Original:
 * Addendum PHP Reflection Annotations
 * http://code.google.com/p/addendum/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 * @author Johno Suchal <johno@jsmf.net>, Sokolov Innokenty <qbbr@qbbr.ru>
 * @copyright Copyright (c) 2006, Jan
 * @copyright Copyright (c) 2011, qbbr
 */
class Q_Annotation
{
    
    public $value;
    
    private static $_creationStack = array();

    
    public final function __construct($data = array(), $target = false)
    {
        $reflection = new ReflectionClass($this);
        $class = $reflection->getName();
        
        if (isset(self::$_creationStack[$class])) {
            trigger_error("Circular annotation reference on '$class'", E_USER_ERROR);
            return;
        }
        
        self::$_creationStack[$class] = true;
        
        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $this->$key = $value;
            } else {
                trigger_error("Property '$key' not defined for annotation '$class'");
            }
        }
        
        $this->checkTargetConstraints($target);
        $this->checkConstraints($target);
        unset(self::$_creationStack[$class]);
    }

    
    private function checkTargetConstraints($target)
    {
        $reflection = new Q_ReflectionAnnotatedClass($this);
        
        if ($reflection->hasAnnotation('Target')) {
            $value = $reflection->getAnnotation('Target')->value;
            $values = is_array($value) ? $value : array($value);

            foreach ($values as $value) {
                if ($value == 'class' && $target instanceof ReflectionClass) return;
                if ($value == 'method' && $target instanceof ReflectionMethod) return;
                if ($value == 'property' && $target instanceof ReflectionProperty) return;
                if ($value == 'nested' && $target === false) return;
            }
            
            if ($target === false) {
                trigger_error("Annotation '" . get_class($this) . "' nesting not allowed", E_USER_ERROR);
            } else {
                trigger_error("Annotation '" . get_class($this) . "' not allowed on " . $this->createName($target), E_USER_ERROR);
            }
        }
    }

    private function createName($target)
    {
        if ($target instanceof ReflectionMethod) {
            return $target->getDeclaringClass()->getName() . '::' . $target->getName();
        } elseif ($target instanceof ReflectionProperty) {
            return $target->getDeclaringClass()->getName() . '::$' . $target->getName();
        } else {
            return $target->getName();
        }
    }

    protected function checkConstraints($target)
    {
    }

}

class Q_AnnotationsCollection
{
    
    private $_annotations;

    
    public function __construct($annotations)
    {
        $this->_annotations = $annotations;
    }

    
    public function hasAnnotation($class)
    {
        $class = Q_Addendum::resolveClassName($class);
        return isset($this->_annotations[$class]);
    }

    
    public function getAnnotation($class)
    {
        $class = Q_Addendum::resolveClassName($class);
        return isset($this->_annotations[$class]) ? end($this->_annotations[$class]) : false;
    }

    
    public function getAnnotations()
    {
        $result = array();

        foreach ($this->_annotations as $instances) {
            $result[] = end($instances);
        }

        return $result;
    }

    
    public function getAllAnnotations($restriction = false)
    {
        $restriction = Q_Addendum::resolveClassName($restriction);
        $result = array();
        
        foreach ($this->_annotations as $class => $instances) {
            if (!$restriction || $restriction == $class) {
                $result = array_merge($result, $instances);
            }
        }
        
        return $result;
    }
}


class Q_AnnotationTarget extends Q_Annotation
{
}


class Q_AnnotationsBuilder
{
    
    private static $_cache = array();

    
    public function build($targetReflection)
    {
        $data = $this->parse($targetReflection);
        $annotations = array();
        
        foreach ($data as $class => $parameters) {
            foreach ($parameters as $params) {
                $annotation = $this->instantiateAnnotation($class, $params, $targetReflection);
                if ($annotation !== false) {
                    $annotations[get_class($annotation)][] = $annotation;
                }
            }
        }
        
        return new Q_AnnotationsCollection($annotations);
    }

    
    public function instantiateAnnotation($class, $parameters, $targetReflection = false)
    {
        $class = Q_Addendum::resolveClassName($class);
        if (is_subclass_of($class, 'Q_Annotation') && !Q_Addendum::ignores($class) || $class == 'Q_Annotation') {
            $annotationReflection = new ReflectionClass($class);
            return $annotationReflection->newInstance($parameters, $targetReflection);
        }
        
        return false;
    }

    
    private function parse($reflection)
    {
        $key = $this->createName($reflection);
        if (!isset(self::$_cache[$key])) {
            $parser = new Q_AnnotationsMatcher;
            $parser->matches($this->getDocComment($reflection), $data);
            self::$_cache[$key] = $data;
        }
        
        return self::$_cache[$key];
    }

    
    private function createName($target)
    {
        if ($target instanceof ReflectionMethod) {
            return $target->getDeclaringClass()->getName() . '::' . $target->getName();
        } elseif ($target instanceof ReflectionProperty) {
            return $target->getDeclaringClass()->getName() . '::$' . $target->getName();
        } else {
            return $target->getName();
        }
    }

    
    protected function getDocComment($reflection)
    {
        return Q_Addendum::getDocComment($reflection);
    }

    
    public static function clearCache()
    {
        self::$_cache = array();
    }
}


class Q_ReflectionAnnotatedClass extends ReflectionClass
{
    
    private $_annotations;

    
    public function __construct($class)
    {
        parent::__construct($class);
        $this->_annotations = $this->createAnnotationBuilder()->build($this);
    }

    
    public function hasAnnotation($class)
    {
        return $this->_annotations->hasAnnotation($class);
    }

    
    public function getAnnotation($annotation)
    {
        return $this->_annotations->getAnnotation($annotation);
    }

    
    public function getAnnotations()
    {
        return $this->_annotations->getAnnotations();
    }

    
    public function getAllAnnotations($restriction = false)
    {
        return $this->_annotations->getAllAnnotations($restriction);
    }

    
    public function getConstructor()
    {
        return $this->createReflectionAnnotatedMethod(parent::getConstructor());
    }

    
    public function getMethod($name)
    {
        return $this->createReflectionAnnotatedMethod(parent::getMethod($name));
    }

    
    public function getMethods($filter = -1)
    {
        $result = array();
        foreach (parent::getMethods($filter) as $method) {
            $result[] = $this->createReflectionAnnotatedMethod($method);
        }
        
        return $result;
    }

    
    public function getProperty($name)
    {
        return $this->createReflectionAnnotatedProperty(parent::getProperty($name));
    }

    
    public function getProperties($filter = -1)
    {
        $result = array();
        foreach (parent::getProperties($filter) as $property) {
            $result[] = $this->createReflectionAnnotatedProperty($property);
        }
        
        return $result;
    }

    
    public function getInterfaces()
    {
        $result = array();
        foreach (parent::getInterfaces() as $interface) {
            $result[] = $this->createReflectionAnnotatedClass($interface);
        }
        
        return $result;
    }

    
    public function getParentClass()
    {
        $class = parent::getParentClass();
        return $this->createReflectionAnnotatedClass($class);
    }

    
    protected function createAnnotationBuilder()
    {
        return new Q_AnnotationsBuilder();
    }

    
    private function createReflectionAnnotatedClass($class)
    {
        return ($class !== false) ? new Q_ReflectionAnnotatedClass($class->getName()) : false;
    }

    
    private function createReflectionAnnotatedMethod($method)
    {
        return ($method !== null) ? new Q_ReflectionAnnotatedMethod($this->getName(), $method->getName()) : null;
    }

    
    private function createReflectionAnnotatedProperty($property)
    {
        return ($property !== null) ? new ReflectionAnnotatedProperty($this->getName(), $property->getName()) : null;
    }

}


class Q_ReflectionAnnotatedMethod extends ReflectionMethod
{
    
    private $_annotations;

    
    public function __construct($class, $name)
    {
        parent::__construct($class, $name);
        $this->_annotations = $this->createAnnotationBuilder()->build($this);
    }
    

    public function hasAnnotation($class)
    {
        return $this->_annotations->hasAnnotation($class);
    }
    

    public function getAnnotation($annotation)
    {
        return $this->_annotations->getAnnotation($annotation);
    }
    

    public function getAnnotations()
    {
        return $this->_annotations->getAnnotations();
    }

    
    public function getAllAnnotations($restriction = false)
    {
        return $this->_annotations->getAllAnnotations($restriction);
    }

    
    public function getDeclaringClass()
    {
        $class = parent::getDeclaringClass();
        return new Q_ReflectionAnnotatedClass($class->getName());
    }
    

    protected function createAnnotationBuilder()
    {
        return new Q_AnnotationsBuilder();
    }

}


class ReflectionAnnotatedProperty extends ReflectionProperty
{
    
    private $_annotations;

    
    public function __construct($class, $name)
    {
        parent::__construct($class, $name);
        $this->_annotations = $this->createAnnotationBuilder()->build($this);
    }

    
    public function hasAnnotation($class)
    {
        return $this->_annotations->hasAnnotation($class);
    }

    
    public function getAnnotation($annotation)
    {
        return $this->_annotations->getAnnotation($annotation);
    }

    
    public function getAnnotations()
    {
        return $this->_annotations->getAnnotations();
    }

    
    public function getAllAnnotations($restriction = false)
    {
        return $this->_annotations->getAllAnnotations($restriction);
    }

    
    public function getDeclaringClass()
    {
        $class = parent::getDeclaringClass();
        return new Q_ReflectionAnnotatedClass($class->getName());
    }

    
    protected function createAnnotationBuilder()
    {
        return new Q_AnnotationsBuilder();
    }

}


class Q_Addendum
{

    private static $_rawMode;
    
    private static $_ignore;
    
    private static $_classnames = array();
    
    private static $_annotations = false;
    

    public static function getDocComment($reflection)
    {
        if (self::checkRawDocCommentParsingNeeded()) {
            $docComment = new Q_DocComment();
            return $docComment->get($reflection);
        } else {
            return $reflection->getDocComment();
        }
    }

    
    /** Raw mode test */
    private static function checkRawDocCommentParsingNeeded()
    {
        if (self::$_rawMode === null) {
            $reflection = new ReflectionClass('Q_Addendum');
            $method = $reflection->getMethod('checkRawDocCommentParsingNeeded');
            self::setRawMode($method->getDocComment() === false);
        }
        
        return self::$_rawMode;
    }

    
    public static function setRawMode($enabled = true)
    {
        if ($enabled) {
            require_once dirname(__FILE__) . DS . 'Annotations' . DS . 'DocComment.php';
        }
        
        self::$_rawMode = $enabled;
    }

    
    public static function resetIgnoredAnnotations()
    {
        self::$_ignore = array();
    }

    
    public static function ignores($class)
    {
        return isset(self::$_ignore[$class]);
    }

    
    public static function ignore()
    {
        foreach (func_get_args() as $class) {
            self::$_ignore[$class] = true;
        }
    }

    
    public static function resolveClassName($class)
    {
        if (isset(self::$_classnames[$class])) {
            return self::$_classnames[$class];
        }
        
        $matching = array();
        
        foreach (self::getDeclaredAnnotations() as $declared) {
            if ($declared == $class) {
                $matching[] = $declared;
            } else {
                $pos = strrpos($declared, "_$class");
                if ($pos !== false && ($pos + strlen($class) == strlen($declared) - 1)) {
                    $matching[] = $declared;
                }
            }
        }
        
        $result = null;
        
        switch (count($matching)) {
            case 0:
                $result = $class;
                break;
            
            case 1:
                $result = $matching[0];
                break;
            
            default:
                trigger_error("Cannot resolve class name for '$class'. Possible matches: " . join(', ', $matching), E_USER_ERROR);
                break;
        }
        
        self::$_classnames[$class] = $result;

        return $result;
    }

    
    private static function getDeclaredAnnotations()
    {
        if (!self::$_annotations) {
            self::$_annotations = array();
            foreach (get_declared_classes() as $class) {
                if (is_subclass_of($class, 'Q_Annotation') || $class == 'Q_Annotation') {
                    self::$_annotations[] = $class;
                }
            }
        }

        return self::$_annotations;
    }

}