<?php
/**
 * Addendum PHP Reflection Annotations
 * http://code.google.com/p/addendum/
 *
 * Copyright (C) 2006-2009 Jan "johno Suchal <johno@jsmf.net>

 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.

 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.

 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
**/

class Q_CompositeMatcher
{
    
    protected $_matchers = array();
    
    private $_wasConstructed = false;

    
    public function add($matcher)
    {
        $this->_matchers[] = $matcher;
    }

    
    public function matches($string, &$value)
    {
        if (!$this->_wasConstructed) {
            $this->build();
            $this->_wasConstructed = true;
        }

        return $this->match($string, $value);
    }
    

    protected function build()
    {
    }

}


class Q_ParallelMatcher extends Q_CompositeMatcher
{

    protected function match($string, &$value)
    {
        $maxLength = false;
        $result = null;
        foreach ($this->_matchers as $matcher) {
            $length = $matcher->matches($string, $subvalue);
            if ($maxLength === false || $length > $maxLength) {
                $maxLength = $length;
                $result = $subvalue;
            }
        }
        $value = $this->process($result);
        return $maxLength;
    }
    

    protected function process($value)
    {
        return $value;
    }

}


class Q_SerialMatcher extends Q_CompositeMatcher
{
    
    protected function match($string, &$value)
    {
        $results = array();
        $totalLength = 0;
        foreach ($this->_matchers as $matcher) {
            if (($length = $matcher->matches($string, $result)) === false)
                return false;
            $totalLength += $length;
            $results[] = $result;
            $string = substr($string, $length);
        }
        $value = $this->process($results);
        
        return $totalLength;
    }

    
    protected function process($results)
    {
        return implode('', $results);
    }

}


class Q_SimpleSerialMatcher extends Q_SerialMatcher
{
    
    private $_returnPartIndex;

    
    public function __construct($returnPartIndex = 0)
    {
        $this->_returnPartIndex = $returnPartIndex;
    }
    

    public function process($parts)
    {
        return $parts[$this->_returnPartIndex];
    }
}


class Q_RegexMatcher
{
    
    private $_regex;

    
    public function __construct($regex)
    {
        $this->_regex = $regex;
    }

    
    public function matches($string, &$value)
    {
        if (preg_match("/^{$this->_regex}/", $string, $matches)) {
            $value = $this->process($matches);
            return strlen($matches[0]);
        }
        $value = false;

        return false;
    }

    protected function process($matches)
    {
        return $matches[0];
    }

}


class Q_AnnotationsMatcher
{

    public function matches($string, &$annotations)
    {
        $annotations = array();
        $annotationMatcher = new Q_AnnotationMatcher;
        while (true) {
            if (preg_match('/[\*\s](?=@)/', $string, $matches, PREG_OFFSET_CAPTURE)) {
                $offset = $matches[0][1] + 1;
                $string = substr($string, $offset);
            } else {
                return; // no more annotations
            }
            
            if (($length = $annotationMatcher->matches($string, $data)) !== false) {
                $string = substr($string, $length);
                list($name, $params) = $data;
                $annotations[$name][] = $params;
            }
        }
    }

}


class Q_AnnotationMatcher extends Q_SerialMatcher
{

    protected function build()
    {
        $this->add(new Q_RegexMatcher('@'));
        $this->add(new Q_RegexMatcher('[A-Z][a-zA-Z0-9_\\\\]*'));
        $this->add(new Q_AnnotationParametersMatcher);
    }

    
    protected function process($results)
    {
        return array($results[1], $results[2]);
    }

}


class Q_ConstantMatcher extends Q_RegexMatcher
{

    private $_constant;

    
    public function __construct($regex, $constant)
    {
        parent::__construct($regex);
        $this->_constant = $constant;
    }

    
    protected function process($matches)
    {
        return $this->_constant;
    }

}


class Q_AnnotationParametersMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_ConstantMatcher('', array()));
        $this->add(new Q_ConstantMatcher('\(\)', array()));
        $paramsMatcher = new Q_SimpleSerialMatcher(1);
        $paramsMatcher->add(new Q_RegexMatcher('\(\s*'));
        $paramsMatcher->add(new Q_AnnotationValuesMatcher);
        $paramsMatcher->add(new Q_RegexMatcher('\s*\)'));
        $this->add($paramsMatcher);
    }

}


class Q_AnnotationValuesMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_AnnotationTopValueMatcher);
        $this->add(new Q_AnnotationHashMatcher);
    }

}


class Q_AnnotationTopValueMatcher extends Q_AnnotationValueMatcher
{

    protected function process($value)
    {
        return array('value' => $value);
    }

}


class Q_AnnotationValueMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_ConstantMatcher('true', true));
        $this->add(new Q_ConstantMatcher('false', false));
        $this->add(new Q_ConstantMatcher('TRUE', true));
        $this->add(new Q_ConstantMatcher('FALSE', false));
        $this->add(new Q_ConstantMatcher('NULL', null));
        $this->add(new Q_ConstantMatcher('null', null));
        $this->add(new Q_AnnotationStringMatcher);
        $this->add(new Q_AnnotationNumberMatcher);
        $this->add(new Q_AnnotationArrayMatcher);
        $this->add(new Q_AnnotationStaticConstantMatcher);
        $this->add(new Q_NestedAnnotationMatcher);
    }

}

class Q_AnnotationKeyMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_RegexMatcher('[a-zA-Z][a-zA-Z0-9_]*'));
        $this->add(new Q_AnnotationStringMatcher);
        $this->add(new Q_AnnotationIntegerMatcher);
    }

}

class Q_AnnotationPairMatcher extends Q_SerialMatcher
{

    protected function build()
    {
        $this->add(new Q_AnnotationKeyMatcher);
        $this->add(new Q_RegexMatcher('\s*=\s*'));
        $this->add(new Q_AnnotationValueMatcher);
    }

    protected function process($parts)
    {
        return array($parts[0] => $parts[2]);
    }

}

class Q_AnnotationHashMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_AnnotationPairMatcher);
        $this->add(new Q_AnnotationMorePairsMatcher);
    }

}

class Q_AnnotationMorePairsMatcher extends Q_SerialMatcher
{

    protected function build()
    {
        $this->add(new Q_AnnotationPairMatcher);
        $this->add(new Q_RegexMatcher('\s*,\s*'));
        $this->add(new Q_AnnotationHashMatcher);
    }

    
    protected function match($string, &$value)
    {
        $result = parent::match($string, $value);
        return $result;
    }

    
    public function process($parts)
    {
        return array_merge($parts[0], $parts[2]);
    }

}


class Q_AnnotationArrayMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_ConstantMatcher('{}', array()));
        $valuesMatcher = new Q_SimpleSerialMatcher(1);
        $valuesMatcher->add(new Q_RegexMatcher('\s*{\s*'));
        $valuesMatcher->add(new Q_AnnotationArrayValuesMatcher);
        $valuesMatcher->add(new Q_RegexMatcher('\s*}\s*'));
        $this->add($valuesMatcher);
    }

}

class Q_AnnotationArrayValuesMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_AnnotationArrayValueMatcher);
        $this->add(new Q_AnnotationMoreValuesMatcher);
    }

}

class Q_AnnotationMoreValuesMatcher extends Q_SimpleSerialMatcher
{

    protected function build()
    {
        $this->add(new Q_AnnotationArrayValueMatcher);
        $this->add(new Q_RegexMatcher('\s*,\s*'));
        $this->add(new Q_AnnotationArrayValuesMatcher);
    }

    
    protected function match($string, &$value)
    {
        $result = parent::match($string, $value);
        return $result;
    }

    
    public function process($parts)
    {
        return array_merge($parts[0], $parts[2]);
    }

}

class Q_AnnotationArrayValueMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_AnnotationValueInArrayMatcher);
        $this->add(new Q_AnnotationPairMatcher);
    }

}

class Q_AnnotationValueInArrayMatcher extends Q_AnnotationValueMatcher
{

    public function process($value)
    {
        return array($value);
    }

}

class Q_AnnotationStringMatcher extends Q_ParallelMatcher
{

    protected function build()
    {
        $this->add(new Q_AnnotationSingleQuotedStringMatcher);
        $this->add(new Q_AnnotationDoubleQuotedStringMatcher);
    }

}


class Q_AnnotationNumberMatcher extends Q_RegexMatcher
{

    public function __construct()
    {
        parent::__construct("-?[0-9]*\.?[0-9]*");
    }

    
    protected function process($matches)
    {
        $isFloat = strpos($matches[0], '.') !== false;
        return $isFloat ? (float) $matches[0] : (int) $matches[0];
    }

}


class Q_AnnotationIntegerMatcher extends Q_RegexMatcher
{

    public function __construct()
    {
        parent::__construct("-?[0-9]*");
    }
    

    protected function process($matches)
    {
        return (int) $matches[0];
    }

}


class Q_AnnotationSingleQuotedStringMatcher extends Q_RegexMatcher
{

    public function __construct()
    {
        parent::__construct("'([^']*)'");
    }
    

    protected function process($matches)
    {
        return $matches[1];
    }

}


class Q_AnnotationDoubleQuotedStringMatcher extends Q_RegexMatcher
{

    public function __construct()
    {
        parent::__construct('"([^"]*)"');
    }

    
    protected function process($matches)
    {
        return $matches[1];
    }

}


class Q_AnnotationStaticConstantMatcher extends Q_RegexMatcher
{
    
    public function __construct()
    {
        parent::__construct('(\w+::\w+)');
    }
    

    protected function process($matches)
    {
        $name = $matches[1];
        if (!defined($name)) {
            trigger_error("Constant '$name' used in annotation was not defined.");
            return false;
        }
        
        return constant($name);
    }

}


class Q_NestedAnnotationMatcher extends Q_AnnotationMatcher
{

    protected function process($result)
    {
        $builder = new Q_AnnotationsBuilder;
        return $builder->instantiateAnnotation($result[1], $result[2]);
    }

}