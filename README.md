# [WIP] Contentful activator for flagception

```php
// YourClass.php

class YourClass
{
    public function check(Client $client)
    {
        // We need an loader which fetch all flags from contentful
        // The client instance and mapping infos are giving.
        $loader = new ContentfulLoader($client, [
            'content_type' => 'FeatureToggle',
            'field_name' => 'name',
            'field_default' => 'default',
            'field_constraint' => 'constraint'
        ]);
        
        $expressionLanguage = new ExpressionLanguage();
        $constraintResolver = new ConstraintResolver($expressionLanguage);
        
        $activator = new ContentfulActivator($loader, $constraintResolver);
        
        if ($activator->isActive('feature_name_for_contentful')) {
            // Do something ...
        }
    }
}

```