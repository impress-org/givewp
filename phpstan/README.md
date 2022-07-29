# GiveWP custom extensions for PHPStan

* [PHPStan](https://phpstan.org/)

## Custom Extensions

- `Reflection\LogMethodsReflectionExtension` adds support for virtual static method calls to `Give\Log\Log`.

## Usage

Include extension.neon in `phpstan.neon`:

```
includes:
	- phpstan/extension.neon
```
