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
    // Access GiveWP admin components from the global window object
    const { AdminSection, AdminSectionField } = window.givewp.admin.components;

    return (
        <Fill name="GiveWP/DonorDetails/Profile/Sections">
            <AdminSection
                title="Your Section Title"
                description="A description of what this section contains."
            >
                <AdminSectionField subtitle="Field Label">
                    <p>Your custom content goes here.</p>
                </AdminSectionField>

                <AdminSectionField subtitle="Another Field">
                    <div>
                        <p>You can add any React components or HTML here.</p>
                        <button>Custom Button</button>
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
