<?php
namespace ERD\SearchBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

use ERD\SearchBundle\Search\IndexManager;
use ERD\SearchBundle\Provider\AggregateEntityProvider;

/**
 * Warms the cache with a built index, which has documents from the aggregate entity provider.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 8, 2012 Ethan Resnick Design
 */
class IndexCacheWarmer extends CacheWarmer implements CacheWarmerInterface
{
    /**
     * @var IndexManager 
     */
    protected $im;

    public function __construct(IndexManager $im)
    {
        $this->im = $im;
    }

    public function isOptional()
    {
        return true; 
    }
    
    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $this->im->rebuild();
    }
}