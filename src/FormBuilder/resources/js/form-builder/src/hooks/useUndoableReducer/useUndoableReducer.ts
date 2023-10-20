import { useReducer, useCallback, Reducer } from 'react';
import { diff, addedDiff, deletedDiff, updatedDiff, detailedDiff } from 'deep-object-diff';

export enum UndoableHistoryTypes {
    REDO = 'redo',
    UNDO = 'undo',
}

interface UndoableConfig {
    maxHistory?: number;
    ignoreInitialState?: boolean;
    filterActionTypes?: (action: string) => boolean;
}

type UndoAction = {
    type: UndoableHistoryTypes.UNDO;
};

type RedoAction = {
    type: UndoableHistoryTypes.REDO;
};

export type UndoRedoActions<Base> = Base | UndoAction | RedoAction;

const DEFAULT_MAX_HISTORY = 20;

export const excludeActionTypes = (actionTypes: string[]) => (action: string) =>
{
    console.log('excludeActionTypes', actionTypes, action)
    return actionTypes.indexOf(action) < 0;
}

export const useUndoableReducer = <State, Action>(
    reducer: Reducer<State, UndoRedoActions<Action>>,
    initialPresent: State,
    undoableConfig: UndoableConfig = {
        maxHistory: DEFAULT_MAX_HISTORY,
    }
) => {
    const memoizedReducer = useCallback(undoable(reducer, undoableConfig), []);
    const [state, dispatch] = useReducer(memoizedReducer, {
        past: [],
        present: initialPresent,
        future: [],
        latestUnfiltered: initialPresent,
    });

    const canUndo = undoableConfig.ignoreInitialState
        ? state.past.length > 1
        : state.past.length > 0;
    const canRedo = state.future.length > 0;

    const triggerUndo = () => {
        dispatch({ type: UndoableHistoryTypes.UNDO });
    };

    const triggerRedo = () => {
        dispatch({ type: UndoableHistoryTypes.REDO });
    };

    return {
        state: state.present,
        dispatch,
        canUndo,
        canRedo,
        triggerRedo,
        triggerUndo,
    };
};

const undoable = <State, Action>(
    reducer: Reducer<State, UndoRedoActions<Action>>,
    undoableConfig: UndoableConfig
) => (
    state: {
        past: State[];
        present: State;
        future: State[];
        latestUnfiltered: State;
    },
    action: UndoRedoActions<Action>
) => {
    const { past, present, future, latestUnfiltered } = state;

    // @ts-ignore
    if (!('type' in action)) {
        return state;
    }

    const filtered =
        undoableConfig.filterActionTypes &&
        !undoableConfig.filterActionTypes(action.type);

    switch (action.type) {
        case UndoableHistoryTypes.UNDO: {
            const previous = past[past.length - 1];
            const newPast = past.slice(0, past.length - 1);

            return {
                past: newPast,
                present: previous,
                future: [latestUnfiltered, ...future],
                latestUnfiltered: previous,
            };
        }

        case UndoableHistoryTypes.REDO: {
            const next = future[0];
            const newFuture = future.slice(1);

            return {
                past: [...past, latestUnfiltered],
                present: next,
                future: newFuture,
                latestUnfiltered: next,
            };
        }

        default: {
            const newPresent = reducer(present, action);

            if (filtered) {
                return {
                    past,
                    present: newPresent,
                    future,
                    latestUnfiltered: present,
                };
            }

            if (present === newPresent) {
                return state;
            }

            return {
                past:
                    past.length === undoableConfig.maxHistory
                        ? [...past, latestUnfiltered].slice(1)
                        : [...past, latestUnfiltered],
                present: newPresent,
                future: [],
                latestUnfiltered: newPresent,
            };
        }
    }
};
