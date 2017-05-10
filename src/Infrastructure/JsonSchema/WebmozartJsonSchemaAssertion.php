<?php
declare(strict_types = 1);

namespace Prooph\Workshop\Infrastructure\JsonSchema;

use Prooph\Workshop\Model\JsonSchema\JsonSchemaAssertion;
use Webmozart\Json\JsonValidator;

final class WebmozartJsonSchemaAssertion implements JsonSchemaAssertion
{
    private static $jsonValidator;

    public function assert(array $data, array $jsonSchema)
    {
        $enforcedObjectData = json_decode(json_encode($data));
        $jsonSchema = json_decode(json_encode($jsonSchema));

        $errors = $this->jsonValidator()->validate($enforcedObjectData, $jsonSchema);

        if (count($errors)) {
            throw new \InvalidArgumentException(
                "Message validation failed: " . implode("\n", $errors),
                400
            );
        }
    }

    private function jsonValidator(): JsonValidator
    {
        if (null === self::$jsonValidator) {
            self::$jsonValidator = new JsonValidator();
        }

        return self::$jsonValidator;
    }
}
