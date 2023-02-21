# ListTablePage

## \<ListTablePage/\> Props

| Prop       | Type                                        | Required | Default             |
|------------|---------------------------------------------|----------|---------------------|
| data       | Object: {items: Array}                      | Yes      | -                   |
| columns    | Array\<ListTableColumn\>                    | Yes      | -                   |
| title      | String                                      | Yes      | -                   |
| singleName | String                                      | No       | __('item', 'give')  |
| pluralName | String                                      | No       | __('items', 'give') |
| error      | Object/Bool                                 | No       | false               |
| isLoading  | Bool                                        | No       | false               |
| align | String - `'start'`, `'center'`, or `'end'`  | No       | 'start'             |
| rowActions | JSX function component                      | No       | null                |
| bulkActions | Array\<BulkActionsConfig\>                  | No | null                |
| apiSettings    | Object: {apiRoot: string, apiNonce: string} | Yes      | -       |
| filterSettings | Array\<FilterConfig\>                       | No       | [ ]     |
| children | ReactNode                                   | No | null    |

### data

`data` is a plain JS object. It has one required property,
`data.items`, which is an array of objects where each object represents a row of data in the table. If you're using SWR,
this should be returned from the `useSWR` hook.

### columns

An array that determines how columns will be displayed. By default, the columns will be displayed in array order.

Each object in the array should contain the following properties:

| Property    | Type                                         | Required? | Description                                                                                                                                             |
|-------------|----------------------------------------------|-----------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| name        | String                                       | Yes       | The property from `data.items` to associate the column with.                                                                                            |
| text        | String                                       | Yes       | The label to display for the column. Should be translatable.                                                                                            |
| preset      | String                                       | No        | Which default component we should use to render this column. If `preset` and `render` are both missing, the column will just display as a plain string. |
| render      | Function                                     | No        | A React functional component that takes one argument, `item`, and returns some JSX .                                                                    |
| addClass    | String                                       | No        | A class name that will be added to the `<td>` element that wraps each table cell.                                                                       |
| inlineSize  | String<br/>(CSS length)                      | No        | The width of the column. Takes any valid CSS length, like `'300px'` or `'50%'`. Default is `'8rem'`.                                                    |
| alignColumn | String<br/>`'start'`, `'center'`, or `'end'` | No        | Alignment of the column's contents. If the `align` prop of `<ListTablePage/>` is set, will override it for this column only.                            |                                                                                   |                                                                                                                             |

### title

Will be used as the title displayed at the top of the page. Also used to generate a visually hidden table caption for
accessibility. Should be translatable.

### singleName

A lowercase string containing the term for a single table item. Will only be displayed if there's an error while
updating the table. Should be translatable.

### pluralName

A lowercase string containing the term for multiple table items. Will only be displayed if there's an error while
updating the table. Should be translatable.

### error

Whether to render the page in an error state. If you're using this without SWR, you can just treat `error` as a boolean.
If you're using SWR, this should be returned from the `useSWR` hook.

### isLoading

Whether to show a loading overlay over the table contents. Row actions are not available while loading. If you're using
SWR, this should be the same as `isValidating` returned from the `useSWR` hook.

### apiSettings

An object with two properties: `apiNonce`, the nonce used to authenticate with the Wordpress REST API, and `apiRoot`,
the root URL that this page will use for fetching data.

### filterSettings

An array that determines what inputs to display above the table. By default, the filters will be displayed in array
order.

Each object in the array should contain the following properties:

| Property | Type               | Required?                        | Description                                                                                                       |
|----------|--------------------|----------------------------------|-------------------------------------------------------------------------------------------------------------------|
| name     | String             | Yes                              | The property from `data.items` to associate the column with.                                                      |
| type     | String             | Yes                              | What kind of filter to display.  <br/> Available types: `select`, `formselect`, `search`                          |
| ariaLabel | String             | Yes                              | A description of the filter for screen readers.                                                                   |
| inlineSize | String<br/>(CSS Length) | No                               | The inline size of the filter. Corresponds to the width in left-to-right languages. Accepts any valid CSS length. |
| options  | Array              | For type `select` or `formselect` | What options to include for the `select`. An array of objects that look like `{value: 1, text: 'option 1'}`       |
| text | String             | No | Used as a placeholder value for `select` or `search` filters                                                      |

### rowActions

A React functional component. Used to display markup that will show when hovering over a row. Takes the following props:

| Prop      | Type                   | Description                                                                                                                                                                                                              |
|-----------|------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| data      | Object: {items: Array} | The data object.  <br/> See the `data` entry above.                                                                                                                                                                      |
| item      | Object                 | The information for a specific row.  <br/> See the `data` entry above.                                                                                                                                                   |
| addRow    | Function               | Show an animation of an item being added to the table.  <br/> Takes a callback (for example, a fetch to an API that will create a new row), returns a function which executes the callback and shows the animation.      |
| removeRow | Function               | Show an animation of an item being removed from the table.  <br/> Takes a callback (for example, a request to an API to that will delete a row), returns a function which executes the callback and shows the animation. |

### bulkActions

An array that determines what bulk actions display above the table. Will be displayed in array order.

When a user selects a bulk action and one or more row checkboxes then clicks 'Apply', a confirmation modal will pop up.
After clicking 'Confirm' in that modal, the action will be applied.

Each object in the array should contain the following properties:

| Property  | Type                                                   | Required? | Description                                                                                                                                                                                                                                                                                                                                     |
|-----------|--------------------------------------------------------|-----------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| label     | String                                                 | Yes       | The label for the bulk action option. Also used for the title of the confirmation window. Should be translatable.                                                                                                                                                                                                                               |
| value     | String or Number                                       | Yes       | The internal value for the bulk action option.<br/>Each `value` should be unique.                                                                                                                                                                                                                                                               |
| action    | Function: (selected) => Promise\<{errors, successes}\> | Yes       | The function that gets run when the user has confirmed the bulk action. Takes one argument, `selected`, which contains an array of IDs for the selected items. Returns an object with the number of successful and failed changes.                                                                                                              |
| confirm   | Function: (selected, names) => (String or JSX element) | Yes       | A string or React component that gets displayed in the confirmation modal. Takes two arguments: `selected`, which contains an array of IDs for the selected items, and `names`, an array of names. Both values are strings. The `names` array will only contain elements if the `item` object (see above) has a `name` or `donorName` property. |
| isVisible | Function<br/> (data, options) => Boolean               | No        | What options to include for the `select`. An array of objects that look like `{value: 1, text: 'option 1'}`                                                                                                                                                                                                                                     |
| type      | String<br/>`'normal'`, `'warning'`, or `'danger'`      | No        | Determines what type of styling to apply to the bulk action confirmation modal.<br/>`'normal'` (default) - green border<br/>`'warning'` - yellow border<br/>`'danger'` - red border                                                                                                                                                             |
