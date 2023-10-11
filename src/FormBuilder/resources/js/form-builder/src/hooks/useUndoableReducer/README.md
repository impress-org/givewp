# use-undoable-reducer

A react hook to add undo and redo history to a reducer from `useReducer`.

Forked from [use-undoable-reducer](https://github.com/alexknipfer/use-undoable-reducer) by [alexknipfer](https://github.com/alexknipfer)

## Basic Usage

```js
export function MyApp() {
  const {
    state,
    dispatch,
    canRedo,
    canUndo,
    triggerRedo,
    triggerUndo,
  } = useUndoableReducer(reducer, initialState);

  return (
    <div>
      <button disabled={!canUndo} onClick={triggerUndo}>
        Undo
      </button>
      <button disabled={!canRedo} onClick={triggerRedo}>
        Redo
      </button>
    </div>
  );
}
```

| Property    | Type     | Description                                                                    |
| ----------- | -------- | ------------------------------------------------------------------------------ |
| state       | object   | Current state object.                                                          |
| dispatch    | function | Dispatcher function returned from `useReducer`                                 |
| canRedo     | boolean  | Indicates if there are any contents in the history stack that can be restored. |
| canUndo     | boolean  | Indicates if there are any contents in the history stack.                      |
| triggerRedo | function | Redo the previous action.                                                      |
| triggerUndo | function | Undo the previous action.                                                      |

## Options

```js
const {
  state,
  dispatch,
  canRedo,
  canUndo,
  triggerRedo,
  triggerUndo,
} = useUndoableReducer(reducer, initialState, {
  maxHistory: 10,
  filterActionTypes: excludeActionTypes(['my_action_to_ignore']),
  ignoreInitialState: true,
});
```

| Option             | Type     | Default   | Description                                             |
| ------------------ | -------- | --------- | ------------------------------------------------------- |
| maxHistory         | number   | 20        | The maximum number of items to keep in undo history.    |
| filterActionTypes  | function | undefined | Actions to be ignored from undo history.                |
| ignoreInitialState | boolean  | undefined | Ignore the first state update and don't add to history. |

## excludeActionTypes

`excludeActionTypes` is a helper function exposed to be used with `filterActionTypes`. You can pass in an array of actions that you want to be ignored from undo history.

Type signature:

```ts
excludeActionTypes(actions: Array<string>) => void;
```
