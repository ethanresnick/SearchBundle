<?php
namespace ERD\SearchBundle\Provider;

/**
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright May 22, 2012 Ethan Resnick Design
 */
interface SynonymProviderInterface
{
    /**
     * @return array An associative array of [word]=>[synonym] sets. 
     */
    public function getSynonyms();
}