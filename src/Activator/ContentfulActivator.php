<?php

namespace Flagception\Contentful\Activator;

use Flagception\Activator\FeatureActivatorInterface;
use Flagception\Constraint\ConstraintResolverInterface;
use Flagception\Contentful\Loader\ContentfulLoaderInterface;
use Flagception\Model\Context;

/**
 * Class ContentfulActivator
 *
 * @author Michel Chowanski <chowanski@bestit-online.de>
 * @package Flagception\Contentful\Activator
 */
class ContentfulActivator implements FeatureActivatorInterface
{
    /**
     * The contentful loader
     *
     * @var ContentfulLoaderInterface
     */
    private $loader;

    /**
     * The constraint resolver
     *
     * @var ConstraintResolverInterface
     */
    private $constraintResolver;

    /**
     * ContentfulActivator constructor.
     *
     * @param ContentfulLoaderInterface $loader
     * @param ConstraintResolverInterface $constraintResolver
     */
    public function __construct(ContentfulLoaderInterface $loader, ConstraintResolverInterface $constraintResolver)
    {
        $this->loader = $loader;
        $this->constraintResolver = $constraintResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'contentful';
    }

    /**
     * {@inheritdoc}
     */
    public function isActive($name, Context $context)
    {
        $flags = $this->loader->load();

        if (!in_array($name, $flags, true)) {
            return false;
        }

        if ($flags[$name]['default'] === true) {
            return true;
        }

        $constraint = $flags[$name]['constraint'];
        return $constraint !== null && $this->constraintResolver->resolve($constraint, $context) === true;
    }
}
