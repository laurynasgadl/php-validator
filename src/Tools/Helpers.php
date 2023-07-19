<?php

namespace Luur\Validator\Tools;

class Helpers
{
    /**
     * @param string $string
     * @return string
     */
    public static function snakeToCamel($string)
    {
        return lcfirst(
            str_replace('_', '', ucwords(
                    strtolower($string), '_'
                )
            )
        );
    }

    /**
     * @param string $string
     * @return string
     */
    public static function snakeToPascal($string)
    {
        return str_replace('_', '', ucwords(
                strtolower($string), '_'
            )
        );
    }

    /**
     * @param string $string
     * @return string
     */
    public static function camelToSnake($string)
    {
        return ltrim(
            strtolower(
                preg_replace(
                    '/[A-Z]([A-Z](?![a-z]))*/', '_$0', $string
                )
            ), '_');
    }
}
