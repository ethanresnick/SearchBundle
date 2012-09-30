<?php
namespace ERD\SearchBundle\Tests\Search;

/**
 * Description of FieldTest
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 23, 2012 Ethan Resnick Design
 */
class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTypeSupportsKey()
    {
        $stub = new \ERD\SearchBundle\Search\Field('key', 'testValue', 'UTF-8', true, false, true, false); //these boolean vals are totally random
        
        $this->assertEquals($stub->getType(), 'Key');
    }
    
}