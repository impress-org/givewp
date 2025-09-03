# Notice Component

A reusable notice component for displaying informational, warning, and error messages in the GiveWP admin interface.

## Features

- **Three types**: `info`, `warning`, and `error`
- **Flexible content**: Accepts any React content via children
- **Dismiss support**: Optional dismiss button (X) with custom click handler
- **Auto-hide**: Notice automatically hides when dismiss button is clicked
- **Flexible control**: Dismiss button only appears when `dismissHandleClick` is provided
- **Responsive design**: Follows GiveWP design system patterns
- **Accessible**: Proper semantic markup and ARIA support
- **Customizable**: Uses CSS modules for styling

## Usage

```tsx
import Notice from '@givewp/admin/components/Notices';

// Basic usage
<Notice type="warning">
    Your campaign is currently archived.
</Notice>

// With action link
<Notice type="warning">
    Your campaign is currently archived. You can view the campaign details but won't be able to make any changes until it's moved out of archive.
    <strong>
        <a href="#" onClick={() => console.log('Action clicked!')}>
            Move to Active
        </a>
    </strong>
</Notice>

// With dismiss button
<Notice
    type="info"
    dismissHandleClick={() => {
        // Handle dismiss click
        console.log('Dismiss clicked!');
    }}
>
    Changes made to this subscription will only affect future renewals.
</Notice>

// Complex content
<Notice type="error">
    <span>An error occurred while processing your request.</span>
    <div style={{ marginTop: '8px' }}>
        <a href="#" onClick={() => console.log('Retry clicked!')}>Retry</a>
        <span style={{ margin: '0 8px' }}>or</span>
        <a href="#" onClick={() => console.log('Contact support clicked!')}>Contact Support</a>
    </div>
</Notice>

// With spacing (recommended)
<div style={{ marginBottom: 'var(--givewp-spacing-4)' }}>
    <Notice type="info">
        Your message here
    </Notice>
</div>
```
```

## Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `type` | `'info' \| 'warning' \| 'error'` | Yes | The type of notice to display |
| `children` | `React.ReactNode` | Yes | The content to display inside the notice |
| `dismissHandleClick` | `() => void` | No | Function to call when dismiss button is clicked. If provided, shows the dismiss (X) button. The notice will automatically hide when dismissed. |

## Types

### Info Notice
- **Background**: Light blue (`#f0f9ff`)
- **Border**: Blue (`var(--givewp-blue-400)`)
- **Icon**: Info icon

### Warning Notice
- **Background**: Light orange (`#fffaf2`)
- **Border**: Orange (`var(--givewp-orange-400)`)
- **Icon**: Warning icon

### Error Notice
- **Background**: Light red (`#fef2f2`)
- **Border**: Red (`var(--givewp-red-400)`)
- **Icon**: Error icon

## Styling

The component uses CSS modules and follows the GiveWP design system:

- **Spacing**: Uses `var(--givewp-spacing-*)` variables
- **Colors**: Uses `var(--givewp-*-*)` color variables
- **Border radius**: Uses `var(--givewp-rounded-4)` and `var(--givewp-rounded-2)`
- **Typography**: Consistent with admin interface

## Layout Considerations

The Notice component doesn't include external spacing by default. This allows for maximum flexibility in different contexts. When you need spacing around the notice, add it at the usage level:

```tsx
// Recommended: Add spacing at usage level
<div style={{ marginBottom: 'var(--givewp-spacing-4)' }}>
    <Notice type="info">Your message</Notice>
</div>
```

