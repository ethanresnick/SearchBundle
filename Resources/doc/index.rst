Search Bundle
============

The search bundle wraps the EWZSearchBundle to provide a few additional functions:
* A mechanism for adding synonyms that can be used in query expansion 
* A way to use annotations to describe how entities should be indexed
* A hook into Doctrine's event system so that your index can be automatically updated when an entity is changed.
* A cache warmer that, using a set of provided entities, will automatically rebuild the index on cache:clear

Each of these features will be described in turn.

**Adding Synonyms**
Create a new service tagged as "erd_search.synonym_provider" and make sure that it implements 
the synonym provider interface (ERD\SearchBundle\Provider\SynonymProviderInterface), which simply 
requires the object to have a getSynonyms() method that returns an associative array of "word"=>"synonym" pairs.

You can add as many such services as you want. Then, when you get the "erd_search.lucene" service from the
dependency-injection container, its find() method will take your synonyms into account.

**Using annotations**
With EWZ's search bundle, you manually build a Document object for each entity and then add that document to the
index through a method on the main ewz_lucene.search service. With this wrapper bundle, you instead add annotations
to an entity class and then add the entity to the index using an index manager service (erd_search.index_manager),
which reads the annotations to build a Document for the entity and then updates the index.

The annotations on an entity are as follows:

* To mark an entity as indexable, you must give it an ERD\SearchBundle\Mapping\Annotation\Indexable annotation.
  For example: 