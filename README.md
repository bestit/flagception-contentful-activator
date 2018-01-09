# [WIP] Contentful activator for flagception

```php
// YourClass.php

class YourClass
{
    public function check(Client $client)
    {
        // We need two arguments:
        //  1. A contentful client
        //  2. The content type name in contentful
        $activator = new ContentfulActivator($client, 'FeatureToggle');
        
        if ($activator->isActive('feature_name_for_contentful')) {
            // Do something ...
        }
    }
}

```