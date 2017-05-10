<?php
declare(strict_types = 1);

namespace Prooph\Workshop\Model\JsonSchema;

interface JsonSchemaAssertion
{
    public function assert(array $data, array $jsonSchema);
}
