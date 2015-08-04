<?php

namespace Greg\Support\Tool;

class Debug
{
    static public function fixInfo($object, $vars, $full = true)
    {
        $return = [];

        $reflection = new \ReflectionClass($object);

        foreach($reflection->getConstants() as $name => $value) {
            $return[$name . ':constant'] = $value;
        }

        $return = array_merge($return, static::fetchVars($object, $reflection->getStaticProperties(), $full));

        $return = array_merge($return, static::fetchVars($object, $vars, $full));

        return $return;
    }

    static public function fetchVars($object, array $vars = [], $full = true)
    {
        $return = [];

        foreach($vars as $name => $value) {
            $property = new \ReflectionProperty($object, $name);

            $key = [$name];

            $key[] = $property->isPrivate() ? 'private' : $property->isProtected() ? 'protected' : 'public';

            if ($property->isStatic()) {
                $key[] = 'static';
            }

            $return[implode(':', $key)] = (!$full and is_object($value)) ? get_class($value) . ' Object' : $value;
        }

        return $return;
    }
}