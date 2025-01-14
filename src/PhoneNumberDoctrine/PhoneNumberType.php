<?php
declare(strict_types = 1);

namespace Nepada\PhoneNumberDoctrine;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\StringType;
use libphonenumber\PhoneNumberUtil;
use function class_exists;

class PhoneNumberType extends StringType
{

    /**
     * @deprecated Kept for DBAL 3.x compatibility
     */
    public function getName(): string
    {
        return PhoneNumber::class;
    }

    /**
     * @param PhoneNumber|string|null $value
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PhoneNumber
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
            throw class_exists(ValueNotConvertible::class)
                ? ValueNotConvertible::new($value, $this->getName(), null, $exception)
                : throw ConversionException::conversionFailed($value, $this->getName(), $exception);
        }
    }

    /**
     * @param PhoneNumber|string|null $value
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return $value;
        }

        if (! $value instanceof PhoneNumber) {
            try {
                $value = PhoneNumber::parse($value);
            } catch (\Throwable $exception) {
                throw class_exists(InvalidType::class)
                    ? InvalidType::new($value, $this->getName(), ['null', PhoneNumber::class, 'phone number string'], $exception)
                    : ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', PhoneNumber::class, 'phone number string'], $exception);
            }
        }

        return $value->format(PhoneNumberFormat::E164);
    }

    /**
     * @deprecated Kept for DBAL 3.x compatibility
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * @deprecated Kept for DBAL 3.x compatibility
     */
    public function getDefaultLength(AbstractPlatform $platform): int
    {
        return 1 + PhoneNumberUtil::MAX_LENGTH_COUNTRY_CODE + PhoneNumberUtil::MAX_LENGTH_FOR_NSN;
    }

    /**
     * @param array<string, mixed> $fieldDeclaration
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $fieldDeclaration['length'] = $this->getDefaultLength($platform);
        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }

}
