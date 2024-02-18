<?php

namespace Penguin\Component\Container;

class InjectProperties
{
    /**
     * Automatically inject services into object properties.
     */
    public static function handle(object $object): void
    {
        $container = Container::getInstance();
        $properties = (new \ReflectionObject($object))->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $property) {
                if (!$property->isInitialized($object) || empty($property->getValue($object))) {
                    $attributes = $property->getAttributes(Inject::class);
                    if (!empty($attributes)) {
                        $instance = (string) end($attributes)->newInstance();
                        $property->setValue($object, $container->get($instance));
                    }
                }
            }
        }
    }
}