<?php

namespace ERD\SearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ERD\SearchBundle\DependencyInjection\Compiler\LoadProvidersCompilerPass;

class ERDSearchBundle extends Bundle
{
    public function getParent()
    {
        return 'EWZSearchBundle';
    }    

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new LoadProvidersCompilerPass());
    }
}