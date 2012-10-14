<?php
namespace ERD\SearchBundle\Search;
use ERD\SearchBundle\Provider\AggregateSynonymProvider;
use EWZ\Bundle\SearchBundle\Lucene\Lucene;

/**
 * Description of LuceneSearch
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 */
class LuceneSearch extends \EWZ\Bundle\SearchBundle\Lucene\LuceneSearch
{
    /**
     * @var string The path to the Lucene index.
     */
    protected $indexPath;

    /**
     * @var AggregateSynonymProvider the synonym provider
     */
    protected $provider;

    /**
     * @var array The synonyms, as word=>synonym pairs. Lazy loaded from {@link $provider} when needed.
     */
    protected $synonyms = null;

 
    public function __construct($luceneIndexPath, $analyzer, AggregateSynonymProvider $provider)
    {   
        parent::__construct($luceneIndexPath, $analyzer);
        
        $this->indexPath = $luceneIndexPath;
        $this->provider = $provider;
    }
    
    /**
     * Run a query that uses the class's synonyms too.
     * 
     * @param string|\Zend\Search\Lucene\Search\Query\AbstractQuery $query Note that synonym replacement will only work on query strings though, not query objects.
     * @param array $sort An array of sorting options, from {@link http://framework.zend.com/manual/en/zend.search.lucene.searching.html#zend.search.lucene.searching.sorting here}; results will be sorted by relevance if an empty array is provided. 
     * @param boolean $useSynonyms Use the class's synonyms to expand the query.
     * @param boolean $catchExceptions Internally catch and handle any exceptions thrown while trying to execute the query?
     * @return array[\Zend\Search\Lucene\Search\QueryHit]
     */
    public function find($query, array $sort=array(), $useSynonyms=true, $catchExceptions = true)
    {
        //unfortunately, I'm not sure yet how to add synonyms to query objects, only strings.
        if(is_string($query) && $useSynonyms)
        {
            $query = $this->addSynonymsToQueryString($query);
        }

        if(!$catchExceptions)
        {
            return $this->findRaw(array_merge(array($query), $sort));
        }
        
        //Error handling is on.
        try
        {
            try
            {
                $results = $this->findRaw(array_merge(array($query), $sort));
            }
            catch(\Exception $e)
            {
                //if a string query threw the error, try a simplified query that might throw 
                //another error (going to the outer catch block) or that might just work.
                if(is_string($query))
                {
                    $query = preg_replace("/[^\w\s]+/", '', $query); 
                    $results = $this->findRaw(array_merge(array($query), $sort));
                }

                //if a queryObject threw the error, we have to get it to the outer catch block
                //because we have no simplified query to try.
                else
                {
                    throw $e;
                }
            }
        }
        catch(\Exception $e)
        {
            $results = array();
        }

        return $results;
    }
    
    /** Tries to do a find with no exception handling. Used in the main find() method. */
    protected function findRaw($args)
    {
       return call_user_func_array(array($this->index, 'find'), $args); 
    }

    protected function addSynonymsToQueryString($queryString)
    {
        $finalQuery = $queryString; //save to a new var so we can always search the original in our loop.

        //lazy load synonyms on first call
        if($this->synonyms === null)
        {
            $this->synonyms = $this->provider->getSynonyms();
        }

        foreach($this->synonyms as $word => $synonym)
        {
            $pattern = '/\b('.preg_quote($word).')\b|\b('.preg_quote($synonym).')\b/i';
            $matches = array();
   
            if(preg_match($pattern, $queryString, $matches))
            {
                $matches[1] = (isset($matches[1])) ? $matches[1] : '';
                $matches[2] = (isset($matches[2])) ? $matches[2] : '';

                $finalQuery = str_replace($matches[1].$matches[2],'('.$word.' OR '.$synonym.')',$finalQuery);
            }
        }

       return $finalQuery;
    }
    
    /**
     * Empties the index.
     * @return void 
     */
    public function emptyIndex()
    {
        $this->index = Lucene::create($this->indexPath);
    }
}
?>