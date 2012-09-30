<?php

namespace ERD\SearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ERDSearchExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //prep config data
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        //if we're hooking into doctrine, register a service to do so.
        if($config['use_doctrine_events'])
        {
            $subscriber = new Definition($container->getParameter('erd_search.doctrine_subscriber.class'));
            $subscriber->addTag('doctrine.event_subscriber');
            $subscriber->setArguments(array(new Reference('erd_search.index_manager')));
            $subscriber->setPublic(false);
            
            $container->setDefinition('erd_search.doctrine_subscriber', $subscriber);
        }
        
        //if we're using doctrine entities, register a doctrine entities provider as a provider
        if($config['use_doctrine_entities'])
        {
            //sort of janky to do this here rather than in a compiler pass, but that would be a lot more work.
            //and bottom line, I think this whole branch is obsolete in Symfony 2.1, which has some architecture
            //for bundles declaring dependencies.
            if (!class_exists('ERD\DoctrineHelpersBundle\ERDDoctrineHelpersBundle')) 
            {
                throw new \Exception("To have ERDSearchBundle automatically operate on Doctrine's entities, you must first install the ERDDoctrineHelpersBundle.");
            }

            $provider = new Definition($container->getParameter('erd_search.doctrine_provider.class'));
            $provider->addTag('erd_search.entity_provider');
            $provider->setArguments(array(
                    new Reference('doctrine'),
                    new Reference('annotations.file_cache_reader'),
                    $container->getParameter("erd_search.indexable_annotation.class"),
                    true
                ));
            $provider->setPublic(false);
            
            $container->setDefinition('erd_search.doctrine_provider', $provider);
        }
    }
}
