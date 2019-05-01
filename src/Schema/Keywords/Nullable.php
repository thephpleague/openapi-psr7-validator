<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;

class Nullable extends BaseKeyword
{
    /**
     * Allows sending a null value for the defined schema. Default value is false.
     *
     * @param $data
     * @param bool $nullable
     */
    public function validate($data, bool $nullable): void
    {
        try {
            if (!$nullable && ($data === null)) {
                throw new \Exception("Value cannot be null");
            }


        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("nullable", $data, $e->getMessage());
        }
    }
}