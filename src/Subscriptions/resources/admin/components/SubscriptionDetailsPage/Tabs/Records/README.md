# Subscription Records Tab

This tab displays subscription records and includes a dynamic info notice that appears when the form has unsaved changes.

## Features

- **Records Display**: Shows subscription-related records through the `RecordsSlot`
- **Dynamic Info Notice**: Automatically displays an info notice when the form is "dirty" (has unsaved changes)
- **Form State Integration**: Uses react-hook-form's `useFormState` to detect form changes

## Info Notice Implementation

### When it appears:
- The notice appears when `isDirty` is `true` (form has unsaved changes)
- The notice disappears when `isDirty` is `false` (form is clean)

### Notice Content:
- **Type**: Info notice (blue styling)
- **Icon**: Triangle icon (info style)
- **Message**: "Changes made to this subscription will only affect future renewals."
- **Dismissible**: Yes (includes close button with custom dismiss handler)

### Technical Details:

1. **Form State Detection**: Uses `useFormState()` from react-hook-form to detect when the form is dirty
2. **Notification System**: Uses the GiveWP admin notification system (`givewp/admin-details-page-notifications`)
3. **Component Structure**: 
   - Uses the global `Notice` component from `@givewp/admin/components/Notices`
   - `index.tsx` - Main tab component with notice logic

### Code Flow:

```tsx
// 1. Get form state
const { isDirty } = useFormState();

// 2. Watch for changes and show/hide notice
useEffect(() => {
    if (isDirty) {
        dispatch.addNotice({
            id: 'subscription-changes-notice',
            type: 'info',
                            content: (
                    <Notice
                        type="info"
                        message={__(
                            'Changes made to this subscription will only affect future renewals.',
                            'give'
                        )}
                        dismissHandleClick={() => {
                            // Dismiss the notice when X button is clicked
                            dispatch.dismissNotification('subscription-changes-notice');
                        }}
                    />
                ),
        });
    } else {
        dispatch.dismissNotification('subscription-changes-notice');
    }
}, [isDirty, dispatch]);
```

## Styling

The notice uses the GiveWP design system:
- **Background**: Light blue (`#f0f9ff`)
- **Border**: Blue (`var(--givewp-blue-400)`)
- **Icon**: Triangle icon in blue
- **Layout**: Horizontal with icon, message, and close button

## Integration

This notice integrates with the existing subscription details page form system and will appear on any tab when the form has unsaved changes, not just the Records tab.
