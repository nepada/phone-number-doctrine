<?php
declare(strict_types = 1);

namespace NepadaTests\PhoneNumberDoctrine;

use Brick\PhoneNumber\PhoneNumber;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Mockery\MockInterface;
use Nepada\PhoneNumberDoctrine\PhoneNumberType;
use NepadaTests\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * @testCase
 */
class PhoneNumberTypeTest extends TestCase
{

    private PhoneNumberType $type;

    /**
     * @var AbstractPlatform|MockInterface
     */
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        parent::setUp();

        if (Type::hasType(PhoneNumber::class)) {
            Type::overrideType(PhoneNumber::class, PhoneNumberType::class);

        } else {
            Type::addType(PhoneNumber::class, PhoneNumberType::class);
        }

        $type = Type::getType(PhoneNumber::class);
        Assert::type(PhoneNumberType::class, $type);
        $this->type = $type;

        $this->platform = \Mockery::mock(AbstractPlatform::class);
    }

    public function testGetName(): void
    {
        Assert::same(PhoneNumber::class, $this->type->getName());
    }

    public function testRequiresSQLCommentHint(): void
    {
        Assert::true($this->type->requiresSQLCommentHint($this->platform));
    }

    public function testConvertToDatabaseValueFails(): void
    {
        Assert::exception(
            function (): void {
                $this->type->convertToDatabaseValue('foo', $this->platform);
            },
            ConversionException::class,
            sprintf(
                'Could not convert PHP value \'foo\'%%a?%% to type %%S?%%%s%%S?%%. Expected one of the following types: null, Brick\PhoneNumber\PhoneNumber, phone number string%%S?%%',
                PhoneNumber::class,
            ),
        );
    }

    /**
     * @dataProvider getDataForConvertToDatabaseValue
     */
    public function testConvertToDatabaseValueSucceeds(PhoneNumber|string|null $value, ?string $expected): void
    {
        Assert::same($expected, $this->type->convertToDatabaseValue($value, $this->platform));
    }

    /**
     * @return list<mixed[]>
     */
    protected function getDataForConvertToDatabaseValue(): array
    {
        return [
            [
                'value' => null,
                'expected' => null,
            ],
            [
                'value' => PhoneNumber::parse('+420 800 123 456'),
                'expected' => '+420800123456',
            ],
            [
                'value' => '+420 800 123 456',
                'expected' => '+420800123456',
            ],
        ];
    }

    public function testConvertToPHPValueFails(): void
    {
        Assert::exception(
            function (): void {
                $this->type->convertToPHPValue('foo', $this->platform);
            },
            ConversionException::class,
            sprintf('Could not convert database value "foo" to Doctrine Type %%S?%%%s%%S?%%', PhoneNumber::class),
        );
    }

    /**
     * @dataProvider getDataForConvertToPHPValue
     */
    public function testConvertToPHPValueSucceeds(PhoneNumber|string|null $value, ?PhoneNumber $expected): void
    {
        $actual = $this->type->convertToPHPValue($value, $this->platform);
        if ($expected === null) {
            Assert::null($actual);
        } else {
            Assert::type(PhoneNumber::class, $actual);
            Assert::same((string) $expected, (string) $actual);
        }
    }

    /**
     * @return list<mixed[]>
     */
    protected function getDataForConvertToPHPValue(): array
    {
        return [
            [
                'value' => null,
                'expected' => null,
            ],
            [
                'value' => PhoneNumber::parse('+420 800 123 456'),
                'expected' => PhoneNumber::parse('+420800123456'),
            ],
            [
                'value' => '+420 800 123 456',
                'expected' => PhoneNumber::parse('+420800123456'),
            ],
        ];
    }

    public function testGetSQLDeclaration(): void
    {
        $this->platform->shouldReceive('getStringTypeDeclarationSQL')->with(['length' => 21])->andReturn('MOCKVARCHAR');
        $declaration = $this->type->getSQLDeclaration(['length' => 255], $this->platform);
        Assert::same('MOCKVARCHAR', $declaration);
    }

}


(new PhoneNumberTypeTest())->run();
