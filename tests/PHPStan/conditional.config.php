<?php
declare(strict_types = 1);

use Doctrine\DBAL\Types\Exception\ValueNotConvertible;

$config = [];

if (class_exists(ValueNotConvertible::class)) { // DBAL 3.x compatibility
    $config['parameters']['ignoreErrors'][] = [
        'message' => '#^Call to an undefined static method Doctrine\\\\DBAL\\\\Types\\\\ConversionException\\:\\:conversionFailed\\(\\)\\.$#',
        'path' => '../../src/PhoneNumberDoctrine/PhoneNumberType.php',
        'count' => 1,
    ];
    $config['parameters']['ignoreErrors'][] = [
        'message' => '#^Call to an undefined static method Doctrine\\\\DBAL\\\\Types\\\\ConversionException\\:\\:conversionFailedInvalidType\\(\\)\\.$#',
        'path' => '../../src/PhoneNumberDoctrine/PhoneNumberType.php',
        'count' => 1,
    ];
}

return $config;
