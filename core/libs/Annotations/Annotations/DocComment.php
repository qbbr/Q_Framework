<?php
if (!defined('T_NAMESPACE')) define('T_NAMESPACE', 377);

class Q_DocComment
{
    
    private static $_classes = array();
    private static $_methods = array();
    private static $_fields = array();
    private static $_parsedFiles = array();

    
    public static function clearCache()
    {
        self::$_classes = array();
        self::$_methods = array();
        self::$_fields = array();
        self::$_parsedFiles = array();
    }

    
    public function get($reflection)
    {
        if ($reflection instanceof ReflectionClass) {
            return $this->forClass($reflection);
        } elseif ($reflection instanceof ReflectionMethod) {
            return $this->forMethod($reflection);
        } elseif ($reflection instanceof ReflectionProperty) {
            return $this->forProperty($reflection);
        }
    }

    
    public function forClass($reflection)
    {
        $this->process($reflection->getFileName());
        $name = $reflection->getName();
        return isset(self::$_classes[$name]) ? self::$_classes[$name] : false;
    }

    
    public function forMethod($reflection)
    {
        $this->process($reflection->getDeclaringClass()->getFileName());
        $class = $reflection->getDeclaringClass()->getName();
        $method = $reflection->getName();
        return isset(self::$_methods[$class][$method]) ? self::$_methods[$class][$method] : false;
    }

    
    public function forProperty($reflection)
    {
        $this->process($reflection->getDeclaringClass()->getFileName());
        $class = $reflection->getDeclaringClass()->getName();
        $field = $reflection->getName();
        return isset(self::$_fields[$class][$field]) ? self::$_fields[$class][$field] : false;
    }

    
    private function process($file)
    {
        if (!isset(self::$_parsedFiles[$file])) {
            $this->parse($file);
            self::$_parsedFiles[$file] = true;
        }
    }

    protected function parse($file)
    {
        $tokens = $this->getTokens($file);
        $currentClass = false;
        $currentBlock = false;
        $namespace = null;
        $max = count($tokens);
        $i = 0;
        while ($i < $max) {
            $token = $tokens[$i];
            if (is_array($token)) {
                list($code, $value) = $token;
                switch ($code) {
                    case T_NAMESPACE:
                        $namespace = $this->getString($tokens, $i, $max) . '\\';
                        break;

                    case T_DOC_COMMENT:
                        $comment = $value;
                        break;

                    case T_CLASS:
                    case T_INTERFACE:
                        $class = $namespace . $this->getString($tokens, $i, $max);
                        if ($comment !== false) {
                            self::$_classes[$class] = $comment;
                            $comment = false;
                        }
                        break;

                    case T_VARIABLE:
                        if ($comment !== false) {
                            $field = substr($token[1], 1);
                            self::$_fields[$class][$field] = $comment;
                            $comment = false;
                        }
                        break;

                    case T_FUNCTION:
                        if ($comment !== false) {
                            $function = $this->getString($tokens, $i, $max);
                            self::$_methods[$class][$function] = $comment;
                            $comment = false;
                        }

                        break;

                    // ignore
                    case T_WHITESPACE:
                    case T_PUBLIC:
                    case T_PROTECTED:
                    case T_PRIVATE:
                    case T_ABSTRACT:
                    case T_FINAL:
                    case T_VAR:
                        break;

                    default:
                        $comment = false;
                        break;
                }
            } else {
                $comment = false;
            }
            $i++;
        }
    }

    
    private function getString($tokens, &$i, $max)
    {
        do {
            $token = $tokens[$i];
            $i++;
            if (is_array($token)) {
                if ($token[0] == T_STRING) {
                    return $token[1];
                }
            }
        } while ($i <= $max);
        
        return false;
    }

    
    private function getTokens($file)
    {
        return token_get_all(file_get_contents($file));
    }

}