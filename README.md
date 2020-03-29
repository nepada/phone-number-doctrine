Phone number Doctrine type
===========================

[![Build Status](https://travis-ci.org/nepada/phone-number-doctrine.svg?branch=master)](https://travis-ci.org/nepada/phone-number-doctrine)
[![Coverage Status](https://coveralls.io/repos/github/nepada/phone-number-doctrine/badge.svg?branch=master)](https://coveralls.io/github/nepada/phone-number-doctrine?branch=master)
[![Downloads this Month](https://img.shields.io/packagist/dm/nepada/phone-number-doctrine.svg)](https://packagist.org/packages/nepada/phone-number-doctrine)
[![Latest stable](https://img.shields.io/packagist/v/nepada/phone-number-doctrine.svg)](https://packagist.org/packages/nepada/phone-number-doctrine)


Installation
------------

Via Composer:

```sh
$ composer require nepada/phone-number-doctrine
```

Register the type in your bootstrap:
``` php
\Doctrine\DBAL\Types\Type::addType(
    \Nepada\PhonNumberDoctrine\PhonNumberType::NAME,
    \Nepada\PhonNumberDoctrine\PhonNumberType::class
);
```

In Nette with [nettrine/dbal](https://github.com/nettrine/dbal) integration, you can register the types in your configuration:
```yaml
dbal:
    connection:
        types:
            phone_number: Nepada\PhoneNumberDoctrine\PhoneNumberType

```  


Usage
-----

`PhoneNumberType` maps database value to phone number value object (see [brick/phonenumber](https://github.com/brick/phonenumber) for further details) and back. The phone number is stored using E164 format, i.e. a '+' sign followed by a series of digits comprising the country code and national number.

Example usage in the entity:
``` php
use Doctrine\ORM\Mapping as ORM;
use Brick\PhoneNumber\PhoneNumber;

/**
 * @ORM\Entity
 * @ORM\Table(name="contacts")
 */
class Contact
{

    /**
     * @ORM\Column(type="phone_number", nullable=false)
     * @var PhoneNumber
     */
    private $phoneNumber;

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

}
```

Example usage in query builder:
```php
$result = $repository->createQueryBuilder('foo')
    ->select('foo')
    ->where('foo.phoneNumber = :phoneNumber')
     // the parameter value is automatically normalized to +420123456789
    ->setParameter('phoneNumber', '+420 123 456 789', PhoneNumberType::NAME)
    ->getQuery()
    ->getResult()
```
