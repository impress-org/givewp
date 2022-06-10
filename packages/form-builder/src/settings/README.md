# Form Settings

## useFormSettings hook

Example:
```jsx
const [ { goalAmount }, updateSetting ] = useFormSettings()

<NumberControl
    value={ goalAmount }
    onChange={ ( goalAmount ) => updateSetting( { goalAmount: goalAmount } ) }
/>
```
