<?php

namespace Flagception\Contentful\Tests\Activator;

use Flagception\Activator\ArrayActivator;
use Flagception\Activator\FeatureActivatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ContentfulActivatorTest
 *
 * @author Michel Chowanski <chowanski@bestit-online.de>
 * @package Flagception\Contentful\Tests\Activator
 */
class ContentfulActivatorTest extends TestCase
{
    /**
     * Test implement interface
     *
     * @return void
     */
    public function testImplementInterface()
    {
        static::assertInstanceOf(FeatureActivatorInterface::class, new ArrayActivator());
    }
}
