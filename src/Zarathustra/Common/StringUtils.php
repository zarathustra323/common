<?php

namespace Zarathustra\Common;

class StringUtils
{
    /**
     * Convert word into underscore format (e.g. some_name_here).
     *
     * @static
     * @param   string  $word
     * @return  string
     */
    public static function underscore($word)
    {
        if (false !== stristr($word, '-')) {
            $parts = explode('-', $word);
            foreach ($parts as &$part) {
                $part = ucfirst(strtolower($part));
            }
            $word = implode('', $parts);
        }
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $word));
    }
    /**
     * Convert word into dasherized format (e.g. some-name-here).
     *
     * @static
     * @param   string  $word
     * @return  string
     */
    public static function dasherize($word)
    {
        return str_replace('_', '-', self::underscore($word));
    }
    /**
     * Convert word into camelized format (e.g. someNameHere).
     *
     * @static
     * @param   string  $word
     * @return  string
     */
    public static function camelize($word)
    {
        return lcfirst(self::studlify($word));
    }
    /**
     * Convert word into studly caps format (e.g. SomeNameHere).
     *
     * @static
     * @param   string  $word
     * @return  string
     */
    public static function studlify($word)
    {
        return str_replace(" ", "", ucwords(strtr(self::underscore($word), "_", " ")));
    }
}
