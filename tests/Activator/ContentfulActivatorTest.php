<?php

namespace Flagception\Contentful\Tests\Activator;

use Contentful\Delivery\Client;
use Contentful\Delivery\ContentType;
use Contentful\Delivery\ContentTypeField;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Query;
use DateTime;
use Flagception\Activator\FeatureActivatorInterface;
use Flagception\Contentful\Activator\ContentfulActivator;
use Flagception\Contentful\Exception\InvalidEntryValueFormatException;
use Flagception\Contentful\Exception\InvalidMappingException;
use Flagception\Model\Context;
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
        static::assertInstanceOf(
            FeatureActivatorInterface::class,
            new ContentfulActivator($this->createMock(Client::class), 'type')
        );
    }

    /**
     * Test name
     *
     * @return void
     */
    public function testName()
    {
        $activator = new ContentfulActivator($this->createMock(Client::class), 'type');
        static::assertEquals('contentful', $activator->getName());
    }

    /**
     * Test with empty contentful result
     *
     * @return void
     */
    public function testNoContentfulResults()
    {
        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->with(static::callback(function (Query $query) use ($contentType) {
                return $query->getQueryData()['content_type'] === $contentType;
            }))
            ->willReturn([]);

        $activator = new ContentfulActivator($client, $contentType);

        static::assertFalse($activator->isActive('feature_bazz', new Context()));
    }

    /**
     * Test contentful value has wrong format
     *
     * @return void
     */
    public function testFieldValueHasWrongFormat()
    {
        $this->expectException(InvalidEntryValueFormatException::class);

        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->willReturn([
                $this->createDynamicEntry(['name', 'state'], ['feature_bazz', new DateTime()])
            ]);

        $activator = new ContentfulActivator($client, $contentType);

        $activator->isActive('feature_bazz', new Context());
    }

    /**
     * Test contentful value return false
     *
     * @return void
     */
    public function testEntryReturnFalse()
    {
        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->willReturn([
                $this->createDynamicEntry(['name', 'state'], ['feature_bazz', false])
            ]);

        $activator = new ContentfulActivator($client, $contentType);

        static::assertFalse($activator->isActive('feature_bazz', new Context()));
    }

    /**
     * Test contentful value return true
     *
     * @return void
     */
    public function testEntryReturnTrue()
    {
        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->willReturn([
                $this->createDynamicEntry(['name', 'state'], ['feature_bazz', true])
            ]);

        $activator = new ContentfulActivator($client, $contentType);

        static::assertTrue($activator->isActive('feature_bazz', new Context()));
    }

    /**
     * Test unknown feature name
     *
     * @return void
     */
    public function testUnknownFeatureName()
    {
        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->willReturn([
                $this->createDynamicEntry(['name', 'state'], ['feature_foo', true])
            ]);

        $activator = new ContentfulActivator($client, $contentType);

        static::assertFalse($activator->isActive('feature_bazz', new Context()));
    }

    /**
     * Test with custom mapping
     *
     * @return void
     */
    public function testCustomMapping()
    {
        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->willReturn([
                $this->createDynamicEntry(['featureKey', 'isActive'], ['feature_bazz', true])
            ]);

        $activator = new ContentfulActivator($client, $contentType, [
            'state' => 'isActive',
            'name' => 'featureKey'
        ]);

        static::assertTrue($activator->isActive('feature_bazz', new Context()));
    }

    /**
     * Test with multiple features
     *
     * @return void
     */
    public function testMultipleFeatures()
    {
        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->willReturn([
                $this->createDynamicEntry(['name', 'state'], ['feature_foo', true]),
                $this->createDynamicEntry(['name', 'state'], ['feature_bazz', false]),
                $this->createDynamicEntry(['name', 'state'], ['feature_foo-bar', true]),
                $this->createDynamicEntry(['name', 'state'], ['feature_bar', true])
            ]);

        $activator = new ContentfulActivator($client, $contentType);

        static::assertTrue($activator->isActive('feature_foo', new Context()));
    }

    /**
     * Test with invalid state field
     *
     * @return void
     */
    public function testInvalidStateField()
    {
        $this->expectException(InvalidMappingException::class);

        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->willReturn([
                $this->createDynamicEntry(['name', 'state'], ['feature_bazz', true])
            ]);

        $activator = new ContentfulActivator($client, $contentType, [
            'state' => 'invalid_state_field'
        ]);

        $activator->isActive('feature_bazz', new Context());
    }

    /**
     * Test with invalid name field
     *
     * @return void
     */
    public function testInvalidNameField()
    {
        $this->expectException(InvalidMappingException::class);

        $contentType = 'foo-type';

        $client = $this->createMock(Client::class);
        $client
            ->expects(static::once())
            ->method('getEntries')
            ->willReturn([
                $this->createDynamicEntry(['name', 'state'], ['feature_bazz', true])
            ]);

        $activator = new ContentfulActivator($client, $contentType, [
            'name' => 'invalid_name_field'
        ]);

        $activator->isActive('feature_bazz', new Context());
    }

    /**
     * Create dynamic entry for testing
     *
     * @param array $fields
     * @param array $values
     *
     * @return DynamicEntry
     */
    private function createDynamicEntry(array $fields, array $values)
    {
        $entry = $this->createMock(DynamicEntry::class);

        $entry
            ->method('getContentType')
            ->willReturn($contentType = $this->createMock(ContentType::class));

        $createdFields = [];
        foreach ($fields as $name) {
            $createdFields[$name] = $this->createMock(ContentTypeField::class);
            $createdFields[$name]
                ->method('getId')
                ->willReturn($name);
        }

        $entry
            ->method('__call')
            ->withConsecutive(...array_map(function ($item) {
                return ['get' . ucfirst($item)];
            }, $fields))
            ->willReturnOnConsecutiveCalls(...$values);

        $contentType
            ->method('getFields')
            ->willReturn($createdFields);

        return $entry;
    }
}
