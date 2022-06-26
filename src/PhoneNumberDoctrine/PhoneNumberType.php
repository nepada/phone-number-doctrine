<?php
declare(strict_types = 1);

namespace Nepada\PhoneNumberDoctrine;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberType extends StringType
{

    public function getName(): string
    {
        return PhoneNumber::class;
    }

    /**
     * @param PhoneNumber|string|null $value
     * @param AbstractPlatform $platform
     * @return PhoneNumber|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhoneNumber
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof PhoneNumber) {
            return $value;
        }

        try {
            return PhoneNumber::parse($value);
        } catch (\Throwable $exception) {
            throw ConversionException::conversionFailed($value, $this->getName(), $exception);
        }
    }

    /**
     * @param PhoneNumber|string|null $value
     * @param AbstractPlatform $platform
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return $value;
        }

        if (! $value instanceof PhoneNumber) {
            try {
                $value = PhoneNumber::parse($value);
            } catch (\Throwable $exception) {
                throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', PhoneNumber::class, 'phone number string'], $exception);
            }
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
