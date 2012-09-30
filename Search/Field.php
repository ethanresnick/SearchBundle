<?php
namespace ERD\SearchBundle\Search;

/**
 * Add support for our key psuedo-type to the field class
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 21, 2012 Ethan Resnick Design
 */
class Field extends \EWZ\Bundle\SearchBundle\Lucene\Field
{
    /**
     * convience function to find the way the field was created
     * instead of having to check the is* individually
     *
     * @return string
     */
    public function getType()
    {
        if($this->name == 'key')
        {
            return 'Key';
        }
        
        return parent::getType();
    }
}