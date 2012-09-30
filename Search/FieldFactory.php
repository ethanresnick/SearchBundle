<?php
namespace ERD\SearchBundle\Search;
use ERD\SearchBundle\Search\Field;

/**
 * Wraps the stupid global/static field generators that Zend Lucene provides in a factory class
 * that encourages users who need to build fields to inject an instance of this rather than pull
 * a field from those globals.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 21, 2012 Ethan Resnick Design
 */
class FieldFactory
{
    /**
     * @internal I need to rewrite all the methods below (rather than just forwarding them to 
     * Zend\Field::methodName) because Zend's static methods return `new self()`, rather than 
     * new static(), so they always return the zend field class, not my subclass of it with the 
     * added type methods.
     */
    public function unIndexed($name, $value, $encoding="UTF-8")
    {
        if($name=='key') { throw new \InvalidArgumentException("Only a field of type 'key' can be named 'key'."); }
        return new Field($name, $value, $encoding, true, false, false);
    }
    
    public function unStored($name, $value, $encoding="UTF-8")
    {
        if($name=='key') { throw new \InvalidArgumentException("Only a field of type 'key' can be named 'key'."); }
        return new Field($name, $value, $encoding, false, true, true);
    }
    
    public function keyword($name, $value, $encoding="UTF-8")
    {
        if($name=='key') { throw new \InvalidArgumentException("Only a field of type 'key' can be named 'key'."); }
        return new Field($name, $value, $encoding, true, true, false);
    }
    
    public function binary($name, $value)
    {
        if($name=='key') { throw new \InvalidArgumentException("Only a field of type 'key' can be named 'key'."); }
        return new Field($name, $value, '', true, false, false, true);
    }
    
    public function text($name, $value, $encoding="UTF-8")
    {
        if($name=='key') { throw new \InvalidArgumentException("Only a field of type 'key' can be named 'key'."); }
        return new Field($name, $value, $encoding, true, true, true);
    }
    
    /* my added method for key */
    public function key($value, $encoding="UTF-8")
    {
        return new Field('key', $value, $encoding, true, false, false); //same signature as unindexed
    }
    
    public function getValidFieldTypes()
    {
        return array('Keyword', 'UnIndexed','Binary','Text','UnStored', 'Key');
    }   
}