<?php
declare(strict_types = 1);

namespace Prooph\Workshop\Infrastructure\Util;

use Assert\InvalidArgumentException;

final class MessageName
{
    const COMMAND_NS = 'Prooph\Workshop\Model\Command';
    const EVENT_NS = 'Prooph\Workshop\Model\Event';

    public static function toFQCN($messageName): string
    {
        $fqcn = self::COMMAND_NS . '\\' . $messageName;

        if(class_exists($fqcn)) {
            return $fqcn;
        }

        $fqcn = self::EVENT_NS . '\\' . $messageName;

        if(class_exists($fqcn)) {
            return $fqcn;
        }

        throw new InvalidArgumentException('Unknown message name: ' . $messageName, 400, 'message_name', $messageName);
    }

    private function __construct()
    {
        //static usage only
    }
}
