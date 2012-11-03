<?php
namespace ERD\SearchBundle\Search;
use EWZ\Bundle\SearchBundle\Lucene\Document;
use ERD\SearchBundle\Provider\AggregateEntityProvider;
use ERD\SearchBundle\Mapping\Annotation\IndexableHandler;
/**
 * Converts provided data objects to Lucene Documents (by reading annotations), and adds them to (or removes them from) the index. 
 *
 * @author Ethan Resnick
 * @copyright Ethan Resnick Design LLC
 */
class IndexManager
{
    /**
     * @var LuceneSearch Acts as an access point into the index. 
     */
    private $ls;
    
    /**
     * @var AggregateEntityProvider Used to get all the entities that should be indexed.
     */
    private $entityProvider;
    
    /**
     * @var IndexableHandler Processes Field annotations on an indexable entity into lucene Field objects. 
     */
    private $indexableHandler;
    
    public function __construct(LuceneSearch $luceneSearch, AggregateEntityProvider $entityProvider, IndexableHandler $indexableHandler)
    {
        $this->ls = $luceneSearch;
        $this->entityProvider = $entityProvider;
        $this->indexableHandler = $indexableHandler;
    }

    /**
     * Uses the entity providers (in {@link $entityProviders}) to rebuild the index.
     */
    public function rebuild()
    {
        $this->ls->emptyIndex();
        
        $this->addEntities($this->entityProvider->getAllEntities());

        $this->ls->updateIndex();
    }
    
    /**
     * Will update the index with new data since it was last used. 
     * 
     * @param array|\Traversable $add Entities to add to the index
     * @param array|\Traversable $update Entities to update in the index
     * @param array|\Traversable $delete Entities to remove from the index
     */
    public function update($add = array(), array $update = array(), array $delete = array())
    {
        $this->addEntities($add);
        $this->updateEntities($update);
        $this->deleteEntities($delete);

        $this->ls->updateIndex();
    }
    
    /**
     * @internal Keeping this method, updateEntities(), deleteEntities(), protected allows us
     * to only have to commit the index once in update(), rather than in each of those methods.
     */
    protected function addEntities($entities)
    {        
        foreach ($entities as $entity)
        {
            try  //will throw an exception if not an indexable entity 
            { 
                $document = $this->indexableHandler->loadFieldData($entity, new Document());
                $this->ls->addDocument($document);
            }
            catch(\Exception $e) {}
        }
    }
    
    protected function updateEntities($entities)
    {
        foreach ($entities as $entity)
        {
            try  //will throw an exception if not an indexable entity 
            { 
                $document = $this->indexableHandler->loadFieldData($entity, new Document());
                $this->ls->updateDocument($document); 
            }
            catch(\Exception $e) {}
        }            
    }
    
    protected function deleteEntities($entities)
    {
        foreach ($entities as $entity)
        {
            try  //will throw an exception if not an indexable entity 
            { 
                $document = $this->indexableHandler->loadFieldData($entity, new Document());
                $this->ls->deleteDocument($document); 
            }
            catch(\Exception $e) {}
        } 
    }
}
?>