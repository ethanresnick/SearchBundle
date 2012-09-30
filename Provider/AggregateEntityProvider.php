<?php
namespace ERD\SearchBundle\Provider;
/**
 * Returns the merged set of entities provided by each individual entity provider.
 * 
 * The idea is that we want to register a single service that can get all the data needed to
 * build/update the search index, but that data comes from a bunch of different providers. So,
 * this class takes the data from all those providers and merges it together. 
 * 
 * Then, every place in the code that wants to trigger an update of the index can just ask for
 * this single provider and not have to worry about all the providers at work behind the scenes
 * or how to retrieve them.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 16, 2012 Ethan Resnick Design
 */
class AggregateEntityProvider implements EntityProviderInterface
{
    /**
     * @var string The FQCN of the interface that each provider must implement.
     */
    protected $interface = 'ERD\SearchBundle\Provider\EntityProviderInterface';

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
                throw new \InvalidArgumentException('All entity providers must implement '.$this->interface);
            }
            
            $this->providers[] = $provider;
        }
    }
    
    public function getAllEntities()
    {
        $result = array();
        
        foreach($this->providers as $provider)
        {
            $result = array_merge($result, $provider->getAllEntities());
        }
        
        return $result;
    }
}