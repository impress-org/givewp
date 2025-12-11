# Permissions Facade

The Permissions Facade provides a resource-focused API for checking user capabilities in GiveWP. Instead of remembering specific WordPress capability strings, developers can use intuitive methods organized by resource type.

## Usage

```php
use Give\Framework\Permissions\Facades\UserPermissions;

// Check if user can edit donation forms
if (!UserPermissions::donationForms()->canEdit()) {
    throw new Exception('You do not have permission to edit donation forms.');
}

// Check if user can view reports
if (UserPermissions::reports()->canView()) {
    // Show reports...
}

// Check if user can manage settings
if (UserPermissions::settings()->canManage()) {
    // Show settings page...
}
```

## Available Resources and Methods

### Post-Type Resources (CRUD operations)

| Resource | Methods |
|----------|---------|
| `donationForms()` | `canCreate()`, `canView()`, `canEdit()`, `canDelete()` |
| `donations()` | `canCreate()`, `canView()`, `canEdit()`, `canDelete()` |
| `donors()` | `canCreate()`, `canView()`, `canEdit()`, `canDelete()` |
| `campaigns()` | `canCreate()`, `canView()`, `canEdit()`, `canDelete()` |

### Global Resources

| Resource | Methods | Description |
|----------|---------|-------------|
| `reports()` | `canView()`, `canExport()` | View and export GiveWP reports |
| `sensitiveData()` | `canView()` | View sensitive donor data (email, address) |
| `settings()` | `canManage()` | Manage GiveWP settings |

## Admin Override

Users with the `manage_options` capability (typically administrators) automatically have full access to all resources. This is handled internally by the facade.

## Role Capabilities Reference

| Role | Donation Forms | Donations | Reports | Sensitive Data | Settings |
|------|----------------|-----------|---------|----------------|----------|
| `administrator` | Full access | Full access | View, Export | View | Manage |
| `give_manager` | Full access | Full access | View, Export | View | Manage |
| `give_worker` | Create, View, Edit, Delete | Create, Edit | - | - | - |
| `give_accountant` | Create, View, Edit | Create, View, Edit | View, Export | - | - |
| `give_donor` | - | - | - | - | - |

## Extending

To add a new resource permission class:

1. For post-type resources, extend `UserPermission`:

```php
use Give\Framework\Permissions\UserPermission;

class MyResourcePermissions extends UserPermission
{
    public static function getType(): string
    {
        return 'my_resource';
    }
}
```

2. For global capabilities, create a standalone class:

```php
class MyPermissions
{
    public function canDoSomething(): bool
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return current_user_can('my_custom_capability');
    }
}
```

3. Add the resource method to `UserPermissionsFacade`.
