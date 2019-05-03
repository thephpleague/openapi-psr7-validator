<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;

// Purpose of this class is to allow customizable/extendable list of formats
class FormatsContainer
{
    /** @var array */
    private static $list = [
        'string' => [
            'byte'      => StringByte::class,
            'date'      => StringDate::class,
            'date-time' => StringDateTime::class,
            'email'     => StringEmail::class,
            'hostname'  => StringHostname::class,
            'uri'       => StringURI::class,
            'uuid'      => StringUUID::class,
            'ipv4'      => StringIP4::class,
            'ipv6'      => StringIP6::class,
        ],
        'number' => [
            'float'  => NumberFloat::class,
            'double' => NumberDouble::class,
        ],
    ];

    /**
     * Empty the list
     */
    static function flush(): void
    {
        self::$list = [];
    }

    /**
     * Put default formats (shipped with the package)
     */
    static function addDefaults(): void
    {
        # string
        self::registerFormat('string', 'byte', StringByte::class);
        self::registerFormat('string', 'date', StringDate::class);
        self::registerFormat('string', 'date-time', StringDateTime::class);
        self::registerFormat('string', 'email', StringEmail::class);
        self::registerFormat('string', 'hostname', StringHostname::class);
        self::registerFormat('string', 'uri', StringURI::class);
        self::registerFormat('string', 'uuid', StringUUID::class);
        self::registerFormat('string', 'ipv4', StringIP4::class);
        self::registerFormat('string', 'ipv6', StringIP6::class);

        # number
        self::registerFormat('string', 'float', NumberFloat::class);
        self::registerFormat('string', 'double', NumberDouble::class);
    }

    /**
     * Add new format to the list
     *
     * @param string $type
     * @param string $format
     * @param string|callable $fqcn
     */
    static function registerFormat(string $type, string $format, $fqcn): void
    {
        self::$list[$type][$format] = $fqcn;
    }

    /**
     * Return FQCN for the format validation class
     *
     * @param string $type
     * @param string $format
     * @return string|callable|null
     */
    static function getFormat(string $type, string $format)
    {
        return self::$list[$type][$format] ?? null;
    }

}