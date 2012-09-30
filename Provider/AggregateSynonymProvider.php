<?php
namespace ERD\SearchBundle\Provider;
/**
 * Returns the merged set of synonyms provided by each individual synonym provider.
 * 
 * The idea is that we want to register a single service that can get all the data needed to
 * build/update the search index, but that data comes from a bunch of different providers. So,
 * this class takes the data from all those providers and merges it together. 
 * 
 * Then, every place in the code that wants to trigger an update of the index can just ask for
 * this single provider and not have to worry about all the providers at work behind the scenes
 * or how to retrieve and validate them.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 16, 2012 Ethan Resnick Design
 */
class AggregateSynonymProvider implements SynonymProviderInterface
{
    /**
     * @var string The FQCN of the interface that each provider must implement.
     */
    protected $interface = 'ERD\SearchBundle\Provider\SynonymProviderInterface';

    protected $providers = array();

    /**
     * @param array[EntitiesProviderInterface] $providers The individual providers.
     * @throws \InvalidArgumentException When one of the entity providers doesn't implement the interface in {@link $interface}.
     */
    public function __construct(array $providers = array())
    {
        foreach($providers as $provider)
        {
            if(!is_object($provider) || !($provider instanceof $this->interface))
            {
                throw new \InvalidArgumentException('All synonym providers must implement '.$this->interface);
            }
            
            $this->providers[] = $provider;
        }
    }
    
    public function getSynonyms()
    {
        $result = array();
        
        foreach($this->providers as $provider)
        {
            $result = array_merge($result, $provider->getSynonyms());
        }
        
        return $result;
    }
}