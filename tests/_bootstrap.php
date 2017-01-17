<?php
// This is global bootstrap for autoloading

include __DIR__ .'/../src/main/php/lib/vendor/autoload.php';

define('OAUTHCONFIG_SAMPLE_XML_1_4', __DIR__.'/_data/oauthconf-sample.xml');

/**
 * Gets an object's property, even if it is public, protected or private. Use this function only for testing purposes.
 *
 * @param object $obj
 * @param string $name Property name.
 * @return mixed Property value.
 * @throws Exception If property does not exists.
 * @throws InvalidArgumentException
 */
function getProperty($obj, $name)
{
    if (!is_object($obj)) {
        throw new InvalidArgumentException('You must provide a valid object instance.');
    }
    if (!is_string($name) || !$name) {
        throw new InvalidArgumentException('You must provide a valid property name.');
    }
    $class = new \ReflectionClass($obj);
    if (!$class->hasProperty($name)) {
        throw new Exception('Property does not exists in provided object.');
    }
    $property = $class->getProperty($name);
    $property->setAccessible(true);
    return $property->getValue($obj);
}

/**
 * Calls a public, private or protected method. Use this function only for testing purposes.
 *
 * @param object $obj
 * @param string $name Property name.
 * @param array $args Method arguments.
 * @return mixed Property value.
 * @throws Exception If property does not exists.
 * @throws InvalidArgumentException
 */
function callMethod($obj, $name, array $args = [])
{
    if (!is_object($obj)) {
        throw new InvalidArgumentException('You must provide a valid object instance.');
    }
    if (!is_string($name) || !$name) {
        throw new InvalidArgumentException('You must provide a valid method name.');
    }

    $class = new \ReflectionClass($obj);
    if (!$class->hasMethod($name)) {
        throw new Exception('Method does not exists in provided object.');
    }
    $method = $class->getMethod($name);
    $method->setAccessible(true);
    return $method->invokeArgs($obj, $args);
}