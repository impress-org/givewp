GiveBlankSlate
=======

Reusable placeholder component based on existing BlankSlate UI pattern used in the plugin when no data is found. 

![giveblankslate](https://user-images.githubusercontent.com/1039236/36345094-e477006a-144a-11e8-8241-7eb7e129155f.png)

## Usage

Render a placeholder user interface when no data to display

```jsx
<GiveBlankSlate title={ __( 'Title for placeholder' ) }
description={ __( 'Description to provide info' ) }
helpLink>
    <Button isPrimary
        isLarge
        href="">
        { __( 'Sample Button ) }
    </Button>
</GiveBlankSlate>
```

## Props

The component accepts the following props:

### noIcon

If this property is added, Give logo will be displayed

- Type: `bool`
- Required: No

### isLoader

If this property is added, loading animation will be displayed instead of the logo.

- Type: `bool`
- Required: No

### title

If this property is added, a title text will be generated using title property as the content.

- Type: `string`
- Required: No

### description

If this property is added, a description text will be generated using description property as the content.

- Type: `string`
- Required: No

### childern

The content to be displayed within the GiveBlankSlate. Include custom content/HTML/components etc.

- Type: `Element`
- Required: Yes

### helpLink

Display text & link to send users to help page on the website. Content is displayed via custom reusable component "GiveHelpLink"

- Type: `bool`
- Required: no