# Donor Details Page Extension

This guide explains how to extend the GiveWP Donor Details page using WordPress slots and the plugin registration system.

## Overview

This system allows you to add custom sections to the donor details page using WordPress's slot-fill pattern and plugin registration system.

## Basic Setup

### 1. Register Your Plugin

Use WordPress's `registerPlugin` function to register your extension:

```typescript
import { registerPlugin } from '@wordpress/plugins';
import YourCustomSection from './components/YourCustomSection';

/**
 * Extend the Window interface to include givewp admin components
 */
declare global {
    interface Window {
        givewp: {
            admin: {
                components: {
                    AdminSection: React.ComponentType<any>;
                    AdminSectionField: React.ComponentType<any>;
                };
                hooks: {
                    useFormContext: () => any;
                    useFormState: () => any;
                };
            };
        };
    }
}

/**
 * Register your custom donor details page extension
 */
registerPlugin(
    'givewp-{addonName}-{referenceToSlotName}', // Unique plugin name.
    {
        render: YourCustomSection,
        scope: 'givewp-donors-details-page', // Required scope for donor details page
    }
);
```

### 2. Create Your Section Component

Use the `Fill` component to add content to the donor details page:

```typescript
import { Fill } from "@wordpress/components";

export default function YourCustomSection() {
    // Access GiveWP admin components and hooks from the global window object
    const { AdminSection, AdminSectionField } = window.givewp.admin.components;
    const { useFormContext, useFormState } = window.givewp.admin.hooks;

    // Use the form hooks to access form data and state
    const { watch, setValue } = useFormContext();
    const { isDirty, isValid } = useFormState();

    // Example: Watch a specific form field
    const donorEmail = watch('email');

    return (
        <Fill name="GiveWP/DonorDetails/Profile/Sections">
            <AdminSection
                title="Your Section Title"
                description="A description of what this section contains."
            >
                <AdminSectionField subtitle="Field Label">
                    <p>Your custom content goes here.</p>
                    <p>Current email: {donorEmail}</p>
                </AdminSectionField>

                <AdminSectionField subtitle="Form Actions">
                    <div>
                        <p>Form is dirty: {isDirty ? 'Yes' : 'No'}</p>
                        <p>Form is valid: {isValid ? 'Yes' : 'No'}</p>
                        <button
                            onClick={() => setValue('customField', 'new value')}
                        >
                            Update Custom Field
                        </button>
                    </div>
                </AdminSectionField>
            </AdminSection>
        </Fill>
    );
}
```

## Key Components

### registerPlugin Parameters

- **name**: A unique identifier for your plugin extension
- **render**: The React component to render
- **scope**: Must be `'givewp-donors-details-page'` for donor details page extensions

### Fill Component

- **name**: Must be `"GiveWP/DonorDetails/Profile/Sections"` to target the donor details page sections area

### GiveWP Admin Components

Access these components via `window.givewp.admin.components`:

- **AdminSection**: Container for your section with title and description
- **AdminSectionField**: Individual field within a section with subtitle

### GiveWP Admin Hooks

Access these React hooks via `window.givewp.admin.hooks`:

- **useFormContext**: Access to react-hook-form context methods (watch, setValue, getValues, etc.)
- **useFormState**: Access to form state information (isDirty, isValid, errors, etc.)

These hooks provide access to the form context established by the donor details page, allowing you to:
- Read current form values
- Update form fields programmatically
- Check form validation state
- Access form errors and other state

#### Example Hook Usage

```typescript
const { watch, setValue, getValues } = useFormContext();
const { isDirty, isValid, errors } = useFormState();

// Watch specific fields
const donorName = watch('name');
const donorEmail = watch('email');

// Update fields
setValue('customField', 'new value');

// Get all form values
const allValues = getValues();

// Check form state
if (isDirty && isValid) {
    // Form has changes and is valid
}
```

## Example Use Cases

- Add custom donor information displays
- Show integration-specific data
- Display related records or statistics
- Add custom actions or controls

## Important Notes

- The plugin scope `givewp-donors-details-page` is required for donor details page extensions
- All GiveWP admin components are available through the global `window.givewp.admin.components` object
- Your component will be automatically inserted into the donor details page
- Follow WordPress React patterns and accessibility guidelines
