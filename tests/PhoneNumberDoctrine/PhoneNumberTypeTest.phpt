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

    /** @var PhoneNumberType */
    private $type;

    /** @var AbstractPlatform|MockInterface */
    private $platform;

    protected function setUp(): void
    {
        parent::setUp();

        Type::addType(PhoneNumberType::NAME, PhoneNumberType::class);

        /** @var PhoneNumberType $type */
        $type = Type::getType(PhoneNumberType::NAME);
        Assert::type(PhoneNumberType::class, $type);
        $this->type = $type;

        $this->platform = \Mockery::mock(AbstractPlatform::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Type::overrideType(PhoneNumberType::NAME, null);
    }

    public function testGetName(): void
    {
        Assert::same('phone_number', $this->type->getName());
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
            'Could not convert PHP value \'foo\' of type \'string\' to type \'phone_number\'. Expected one of the following types: null, Brick\PhoneNumber\PhoneNumber, phone number string'
        );
    }

    /**
     * @dataProvider getDataForConvertToDatabaseValue
     * @param mixed $value
     * @param string|null $expected
     */
    public function testConvertToDatabaseValueSucceeds($value, ?string $expected): void
    {
        Assert::same($expected, $this->type->convertToDatabaseValue($value, $this->platform));
    }

    /**
     * @return mixed[]
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
            'Could not convert database value "foo" to Doctrine Type phone_number'
        );
    }

    /**
     * @dataProvider getDataForConvertToPHPValue
     * @param mixed $value
     * @param PhoneNumber|null $expected
     */
    public function testConvertToPHPValueSucceeds($value, ?PhoneNumber $expected): void
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
     * @return mixed[]
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
        $this->platform->shouldReceive('getVarcharTypeDeclarationSQL')->with(['length' => 21])->andReturn('MOCKVARCHAR');
        $declaration = $this->type->getSQLDeclaration(['length' => 255], $this->platform);
        Assert::same('MOCKVARCHAR', $declaration);
    }

}


(new PhoneNumberTypeTest())->run();
