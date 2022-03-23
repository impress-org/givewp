#ListTablePage and ListTable

##ListTable

| Prop       | Type                | Required | Default             |
|------------|---------------------|----------|---------------------|
| data       | Object: {items: {}} | Yes      | -                   |
| columns    | Array               | Yes      | -                   |
| title      | String              | Yes      | -                   |
| singleName | String              | No       | __('item', 'give')  |
| pluralName | String              | No       | __('items', 'give') |
| error      | Object/Bool         | No       | false               |
| isLoading  | Bool                | No       | false               |

##ListTablePage
**ListTablePage** takes the same props as **ListTable**, as well as the following:

| Prop           | Type                                        | Required | Default |
|----------------|---------------------------------------------|----------|---------|
| apiSettings    | Object: {apiRoot: string, apiNonce: string} | Yes      | -       |
| filterSettings | Array                                       | No       | [ ]     |


### data
`data` is a plain JS object. It has one required property,
`data.items`, which is an array of objects where each object represents
a row of data in the table. If you're using SWR, this
should be returned from the `useSWR` hook.

### columns
An array that determines how columns will be displayed. By default, the columns will be displayed in array order.


Each object in the array should contain the following properties:

| Property | Type | Required? | Description                                                                                                                                             |
| -------- | ---- |-----------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| name | String | Yes       | The property from `data.items` to associate the column with.                                                                                            |
| text | String | Yes       | The label to display for the column. Should be translatable.                                                                                            |
| preset | String | No | Which default component we should use to render this column. If `preset` and `render` are both missing, the column will just display as a plain string. |
| render | Function | No        | A React functional component that takes one argument, `item`, and returns some JSX .                                                                    |
| addClass | String | No | A class name that will be added to the `<td>` element that wraps each table cell. |

### title
Will be used as the title displayed at the top of the page. Also used to generate a visually hidden table caption for accessibility. Should be translatable.

### singleName
A lowercase string containing the term for a single table item. Will only be displayed if there's an error while updating the table.

### pluralName
A lowercase string containing the term for multiple table items. Will only be displayed if there's an error while updating the table.

### error
Whether to render the page in an error state. If you're using this without SWR, you can just treat `error` as a boolean. If you're using SWR, this
should be returned from the `useSWR` hook.

### isLoading
Whether to show a loading overlay over the table contents. Row actions are not available while loading. If you're using SWR, this should be the same as `isValidating` returned from the `useSWR` hook.

### apiSettings
An object with two properties: `apiNonce`, the nonce used to authenticate with the Wordpress REST API, and `apiRoot`, the root URL that this page will use for fetching data.

### filterSettings
An array that determines what inputs to display above the table. By default, the filters will be displayed in array order.

Each object in the array should contain the following properties:

| Property | Type  | Required?         | Description                                                                                                 |
|----------|-------|-------------------|-------------------------------------------------------------------------------------------------------------|
| name     | String | Yes               | The property from `data.items` to associate the column with.                                                |
| type     | String | Yes               | What kind of filter to display.  <br/> Currently available types: `select`, `search`                        |
| ariaLabel | String | Yes | A description of the filter for screen readers.                                                             |
| options  | Array | For type `select` | What options to include for the `select`. An array of objects that look like `{value: 1, text: 'option 1'}` |
