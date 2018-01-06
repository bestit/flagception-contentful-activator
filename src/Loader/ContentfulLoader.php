<?php

namespace Flagception\Contentful\Loader;

use Contentful\Delivery\Client;
use Contentful\Delivery\ContentTypeField;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Query;
use Flagception\Contentful\Exception\InvalidEntryValueFormatException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContentfulLoader
 *
 * @author Michel Chowanski <chowanski@bestit-online.de>
 * @package Flagception\Contentful\Loader
 */
class ContentfulLoader implements ContentfulLoaderInterface
{
    /**
     * The client
     *
     * @var Client
     */
    private $client;

    /**
     * The mapping fields
     *
     * @var array
     */
    private $mapping;

    /**
     * ContentfulProvider constructor.
     *
     * @param Client $client
     * @param array $mapping
     */
    public function __construct(Client $client, array $mapping)
    {
        $this->client = $client;

        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'content_type',
            'field_name',
            'field_default',
            'field_constraint'
        ]);

        $this->mapping = $resolver->resolve($mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function load()
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

            $flags[$values[$this->mapping['field_name']]] = [
                'default' => filter_var($values[$this->mapping['field_default']], FILTER_VALIDATE_BOOLEAN),
                'constraint' => $values[$this->mapping['field_constraint']]
            ];
        }

        return $flags;
    }
}
