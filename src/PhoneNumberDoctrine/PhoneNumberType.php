<?php
declare(strict_types = 1);

namespace Nepada\PhoneNumberDoctrine;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberParseException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberType extends StringType
{

    public const NAME = 'phone_number';

    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return PhoneNumber|null
     * @throws PhoneNumberParseException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhoneNumber
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof PhoneNumber) {
            return $value;
        }

        return PhoneNumber::parse($value);
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string|null
     * @throws PhoneNumberParseException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return $value;
        }

        if (!$value instanceof PhoneNumber) {
            $value = PhoneNumber::parse($value);
        }

        return $value->format(PhoneNumberFormat::E164);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getDefaultLength(AbstractPlatform $platform): int
    {
        return 1 + PhoneNumberUtil::MAX_LENGTH_COUNTRY_CODE + PhoneNumberUtil::MAX_LENGTH_FOR_NSN;
    }

    /**
     * @param mixed[] $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $fieldDeclaration['length'] = $this->getDefaultLength($platform);
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

}
