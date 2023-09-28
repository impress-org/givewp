import {createContext, ReactNode, useContext, useReducer} from 'react';
import editorStateReducer, {setEditorMode} from './reducer';

export interface EditorState {
    mode: string;
}

const StoreContext = createContext(null);
StoreContext.displayName = 'EditorState';

const StoreContextDispatch = createContext(null);
StoreContextDispatch.displayName = 'EditorStateDispatch';

const EditorStateProvider = ({initialState, children}: { initialState: EditorState; children: ReactNode }) => {
    const [state, dispatch] = useReducer(editorStateReducer, initialState);

    return (
        <StoreContext.Provider value={state}>
            <StoreContextDispatch.Provider value={dispatch}>{children}</StoreContextDispatch.Provider>
        </StoreContext.Provider>
    );
};

const useEditorState = () => useContext<EditorState>(StoreContext);

const useEditorStateDispatch = () => useContext(StoreContextDispatch);

export {EditorStateProvider, useEditorState, useEditorStateDispatch, setEditorMode};
