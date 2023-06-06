## Application Structure

```
src
│   index.js  `The main entrypoint for the build process.`
│   App.js  `The top-level component for the application.`
│
└───blocks
│   └───fields  `Individual fields used in the form builder.`
│   └───sections  `Groupings of fields in the form builder.`
│
└───components
│   └───content
│   └───header
│   └───sidebar
│
└───hooks
│
└───settings  `Form setting components and sub-components`
│
```

## Styling Components

Following the lead of the Gutenberg project, and CSS-in-JS in general, most component styles are managed inline. This co-locates the styles with the JSX for single file management, where applicable.

```jsx
<section style={{marginRight: '20px', display: 'flex', gap: '10px', alignItems: 'center'}}>
    {children}
</section>
```

In some cases, `scss` stylesheets are still used. This is required for CSS features such as `hover` styles and is recommended when intentionally exposing class names as a Style API. Additionally, for "complex" styling (which is open to interpretation) it is okay to break out into an `scss` stylesheet and manage that component's styles with classes.

```
component
│   index.js  `import './style.scss`
│   style.scss
```

### Global Styles and Variables

Globally applicable values should be applied using [CSS custom properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties).

```css
# Definition
:root {
  --givewp-green-brand: #68BF6B;
}

# Usage
element {
  background-color: var(--givewp-green-brand);
}
```
