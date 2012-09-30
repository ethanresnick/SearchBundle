<?php
namespace ERD\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Amends the definition of the ewz_search.lucene service so that it's aware of all the user provided synonyms.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright May 23, 2012 Ethan Resnick Design
 */
class LoadProvidersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach(array('entity', 'synonym') as $type)
        {
            $def  = $container->getDefinition('erd_search.aggregate_'.$type.'_provider');
            $args = $def->getArguments();
        
            $matchingServices = array();        
            foreach($container->findTaggedServiceIds('erd_search.'.$type.'_provider') as $id=>$tag)
            {
                $matchingServices[] = new Reference($id);
            }
        
            //add taged services to the args
            $args[] = $matchingServices;
            $def->setArguments($args);

            //add the service to the container with the definition
            $container->setDefinition('erd_search.aggregate_'.$type.'_provider', $def);
        }
        
        //append synonym provider to main lucene service's args
        $def  = $container->getDefinition('erd_search.lucene');

        $args = $def->getArguments();
        $args[] = new Reference('erd_search.aggregate_synonym_provider');
        $def->setArguments($args);

        $container->setDefinition('erd_search.lucene', $def);
    }
}