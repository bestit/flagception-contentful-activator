<?php

namespace Flagception\Contentful\Loader;

/**
 * Interface ContentfulLoaderInterface
 *
 * @author Michel Chowanski <chowanski@bestit-online.de>
 * @package Flagception\Contentful\Loader
 */
interface ContentfulLoaderInterface
{
    /**
     * Load toggles from contentful
     *
     * @return array
     */
    public function load();
}
