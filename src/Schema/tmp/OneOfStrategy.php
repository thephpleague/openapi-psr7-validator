<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema;

use cebe\openapi\spec\Schema as CebeSchema;
use OpenAPIValidation\Schema\Exception\DataMismatched;

class OneOfStrategy extends SchemaValidationBaseStrategy implements ValidationStrategy
{
    /** @var  CebeSchema */
    protected $schema;

    /**
     * @param CebeSchema $schemas
     */
    public function __construct(CebeSchema $schemas)
    {
        $this->schema = $schemas;
    }


    public function validate($data): void
    {
        $resultsSet = new \SplObjectStorage();

        foreach ($this->schema->oneOf as $schema) {
            try {
                $strategy = parent::getStrategy($schema);
                $strategy->validate($data);

                $resultsSet->attach($schema);
            } catch (DataMismatched $e) {
                // do nothing
            }
        }

        // only one schema exactly must match the data
        if (count($resultsSet) != 1) {
            throw DataMismatched::fromSchema($this->schema, $data);
        }

    }
}