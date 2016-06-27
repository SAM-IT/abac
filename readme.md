# PHP Attribute Based Access Control
A simple framework for implementing ABAC in your application.

# Yii2 Connector.
The Yii2 connector allows storing the permissions in a Yii2 ActiveRecord model.
Configuration:
````
'components' => [
    'abac' => [

        'class' => \SamIT\ABAC\connectors\yii2\Manager::class,
        'ruleDirectory' => __DIR__ . '/../rules';

    'user' => [
        'accessChecker' => 'abac'
    ]
````