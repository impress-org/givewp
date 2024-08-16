# Permissions Facade

The Permissions Facade is a class that allows you to check if a user has a specific capability or role. It is a wrapper around the WordPress `current_user_can()` function.

Example:

```php
use Give\Framework\Permissions\Permissions;

if (!UserPermissions::donationForms()->can('edit')) {
    throw new Exception('You do not have permission to edit donation forms.');
}
```
};
```
