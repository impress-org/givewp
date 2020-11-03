# Test Data Framework

## Providers

Providers are encapsulated wrappers for domain specific random data generators. While a library generates fake data of generic types (ie names, emails, numbers), the domain application has specific data types that wrap these generic types.

For example, `Provider\RandomAmount` wraps the `randomElement` generic to return, randomly, an amount from a pre-selected list. This mirrors the donation amount tiers in GiveWP. As opposed to generating a random number for the donation amount, it is more realistic that donations are made in known quantities matching a tier amount.

Each provider has a single responsibility, a single data type or collection, that it generates on command. This is implemented on the `Provider\ProviderContract` interface, which enforces the `__invoke()` method for a Provider, which allows a Factory to call a loaded provider class as a function call.

```php
class ExampleFactory extends Framework\Factory {
    public function definition() {
        return [
            'foo' => $this->randomAttribute(), // where RandomAttribute is a class in .../Framework/Providers/
        ]
    }
}
```
