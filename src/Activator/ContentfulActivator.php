<?php

namespace Flagception\Contentful\Activator;

use Contentful\Delivery\Client;
use Contentful\Delivery\ContentTypeField;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Query;
use Flagception\Activator\FeatureActivatorInterface;
use Flagception\Contentful\Exception\InvalidEntryValueFormatException;
use Flagception\Model\Context;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContentfulActivator
 *
 * @author Michel Chowanski <chowanski@bestit-online.de>
 * @package Flagception\Contentful\Activator
 */
class ContentfulActivator implements FeatureActivatorInterface
{
    /**
     * The client
     *
     * @var Client
     */
    private $client;

    /**
     * Content type key
     *
     * @var string
     */
    private $contentType;

    /**
     * The mapping fields
     *
     * @var array
     */
    private $mapping;

    /**
     * ContentfulActivator constructor.
     *
     * @param Client $client
     * @param string $contentType
     * @param array $mapping
     */
    public function __construct(Client $client, $contentType, $mapping = [])
    {
        $this->client = $client;
        $this->contentType = $contentType;

        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'name' => 'name',
            'state' => 'state'
        ]);

        $this->mapping = $resolver->resolve($mapping);
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
        $result = $this->client->getEntries((new Query)->setContentType($this->mapping['content_type']));
        $flags = [];

        /** @var DynamicEntry $item */
        foreach ($result as $item) {
            $fields = $item->getContentType()->getFields();

            $values = array_map(function (ContentTypeField $field) use ($item) {
                $entryValue = $item->{'get' . ucfirst($field->getId())}();
                if (!is_string($entryValue) || !is_bool($entryValue)) {
                    throw new InvalidEntryValueFormatException(sprintf(
                        'Entry value must be string or boolean but is "%s"',
                        gettype($entryValue)
                    ));
                }
                return $entryValue;
            }, $fields);

            $state = filter_var($values[$this->mapping['state']], FILTER_VALIDATE_BOOLEAN);
            $flags[$values[$this->mapping['name']]] = $state;
        }

        if (!in_array($name, $flags, true)) {
            return false;
        }

        return $flags[$name]['state'];
    }
}
