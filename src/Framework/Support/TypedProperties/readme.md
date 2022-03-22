# Typed Properties Trait

In certain projects you just find yourself locked into older versions of PHP. You stand on your toes, gazing over the
project fence into other yards, thinking, "it must be nice." PHP is a great language and is evolving, but older versions
simply have terribly type hinting. Especially in classes, typed properties weren't introduced into PHP 7.4.

This little trait is for those that want the powers of typed properties but can't support PHP 7.4.

## How it works

The philosophy of this trait is to provide a way to declare typed properties in such a way that works as closely as
possible to real typed properties in PHP 7.4 and beyond. It introduces a couple extra features, but only as a superset
of how typed properties ultimately work.

Here's an example:

```php
use GiveWP\Framework\Support\TypedProperties\TypedProperties;

class User {
    use TypedProperties;

    protected $properties = [
        'firstName' => 'string',
        'lastName' => 'string',
        'fullName' => 'string:readonly',
        'email' => ['string', 'foo@example.com'],
        'birthday' => DateTime::class,
        'password' => 'string:readonly',
    ];

    public function __construct($firstName, $lastName, $birthday, $password) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->birthday = new DateTime($birthday);
        $this->password = $password;
    }

    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function passwordMatches($password) {
        return $this->password === $password;
    }
}

$bill = new User('Bill', 'Murray', '1950-09-21', 'password');
$bill->fullName; // Bill Murray
$bill->email; // foo@example.com
$bill->passwordMatches('password'); // true
```

First, let's break down the `$properties` array into its options. The first item is the property name, the second is its
type and optional attributes:

```php
$properties = [
    'name' => 'type',
    'name' => 'type:readonly',
    'name' => ['type', 'defaultValue'],
];
```

Note:

- The `type` may be any [PHP type](https://www.php.net/manual/en/language.types.php)
- If a colon us used after the type you may specify whether the property is read-only
- If used as an array, the second item is the default value

## Usage Details

There are a few nuances to be aware of, so please review the following.

### Getters and Setters
By making a function with the prefix of `get` or `set`, followed by the property name, you can simulate computed
properties. Note that the underlying property value is not set unless you do so in your setter.

If you want to get or set the value of a property, the `TypedProperty` is passed to the setter as the second argument
(after the value), and the first argument for the getter.

```php
public function getName(TypedProperty $property) {
    return ucfirst($property->getValue());
}

public function setName($value, TypedProperty $property) {
    $property->setValue(strtolower($value));
}
```

### Looping through properties

A limit of traits is that you cannot implement interfaces. As such, iterable interfaces are not automatically supported.
The implementing class can, of course, implement `Iterator` interface to support iteration. In the meantime, you can use
the `mapProperties` method to loop through the properties:

```php
$user = new User('Bill', 'Murray', '1950-09-21', 'password');
$user->mapProperties(function($name, $value) {
    // do something with $name and $value
});
```

### Force writing property values
You can't.

"Wait, what?"

No, really. You can't. The point of this trait is to act as close to typed properties in PHP 7.4+ as possible. You can't
force values for typed properties then, so you can't here, either. This may seem like a strange limitation, but it's in
place to help guide towards proper structure. Breaking out of types kind of defeats the purpose of types. You can always
set the type to `mixed` if you need flexibility.
