# Contentful activator for flagception
Manage feature flags for [Flagception](https://packagist.org/packages/flagception/flagception) with [Contentful](https://www.contentful.com/)!

[![Latest Stable Version](https://poser.pugx.org/flagception/contentful-activator/v/stable)](https://packagist.org/packages/flagception/contentful-activator)
[![Coverage Status](https://coveralls.io/repos/github/bestit/flagception-contentful-activator/badge.svg?branch=master)](https://coveralls.io/github/bestit/flagception-contentful-activator?branch=master)
[![Build Status](https://travis-ci.org/bestit/flagception-contentful-activator.svg?branch=master)](https://travis-ci.org/bestit/flagception-contentful-activator)
[![Total Downloads](https://poser.pugx.org/flagception/contentful-activator/downloads)](https://packagist.org/packages/flagception/contentful-activator)
[![License](https://poser.pugx.org/flagception/contentful-activator/license)](https://packagist.org/packages/flagception/contentful-activator)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/01317ffd-b126-47d3-beda-495cabd4685a/small.png)](https://insight.sensiolabs.com/projects/01317ffd-b126-47d3-beda-495cabd4685a)

Download the library
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this library:

```console
$ composer require flagception/contentful-activator
```

Usage
---------------------------

Just create a new `ContentfulActivator` instance and commit it to your feature manager:
```php
// YourClass.php

class YourClass
{
    public function run()
    {
        // We need two arguments:
        //  1. A contentful client
        //  2. The content type name in contentful
        $activator = new ContentfulActivator($this->client, 'FeatureToggle');
        
        $manager = new FeatureManager($activator);
        if ($manager->isActive('your_feature_name')) {
            // do something
        }
    }
}
```

The `ContentfulActivator` need three arguments:
* An instance of a [contentful client](https://packagist.org/packages/contentful/contentful)
* The contentful model type id as string
* The field mappings as array ('name' and 'state') - Optional (default values are set)

If your contentful model looks like this ...

```json
{
  "name": "Feature Management",
  "description": "Features verwalten",
  "displayField": "featureName",
  "fields": [
    {
      "id": "featureName",
      "name": "Feature",
      "type": "Text",
      "localized": false,
      "required": true,
      "validations": [],
      "disabled": false,
      "omitted": false
    },
    {
      "id": "isActive",
      "name": "Aktiv",
      "type": "Boolean",
      "localized": false,
      "required": true,
      "validations": [],
      "disabled": false,
      "omitted": false
    }
  ],
  "sys": {
    "space": {
      "sys": {
        "type": "Link",
        "linkType": "Space",
        "id": "9d8smn39"
      }
    },
    "id": "myFeatureModel",
    "type": "ContentType",
    "createdAt": "2017-12-07T15:54:07.255Z",
    "updatedAt": "2018-01-11T16:08:47.283Z",
    //...
  }
}
```

... then your activator instance should be like this:

```php
// YourClass.php

class YourClass
{
    public function run()
    {
        // "myFeatureModel" is the content model type
        $activator = new ContentfulActivator($this->client, 'myFeatureModel', [
            'name' => 'featureName', // Field name for feature key
            'state' => 'isActive' // Field name for feature state
        ]);
        
        $manager = new FeatureManager($activator);
        if ($manager->isActive('your_feature_name')) {
            // do something
        }
    }
}
```

You can skip the field mapping (like the first example) if you use the default field names in contentful:
* 'state' for the feature state field
* 'name' for the feature key field
