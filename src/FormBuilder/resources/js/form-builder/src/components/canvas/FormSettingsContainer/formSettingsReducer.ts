export const formSettingsReducer = (state: State, action: Action) => {
    switch (action.type) {
        case 'SET_CONTENT':
            return {...state, content: action.payload};
        case 'SET_MENU_PAGE':
            return {...state, menuPage: action.payload};
        case 'SET_ACTIVE_MENU':
            return {...state, activeMenu: action.payload};
        case 'PUSH_MENU_STACK':
            return {...state, menuStack: [...state.menuStack, action.payload]};
        case 'POP_MENU_STACK':
            return {...state, menuStack: state.menuStack.slice(0, -2)};
        case 'RESET_MENU_STACK':
            return {...state, menuStack: []};
        default:
            return state;
    }
}

export const setContent = (content: React.ReactNode): Action => ({
    type: 'SET_CONTENT',
    payload: content,
});

export const setMenuPage = (page: number): Action => {
    console.log(page);
    return {
        type: 'SET_MENU_PAGE',
        payload: page,
    };
};

export const setActiveMenu = (menuItem: string): Action => ({
    type: 'SET_ACTIVE_MENU',
    payload: menuItem,
});

export const pushMenuStack = (menuItem: string): Action => ({
    type: 'PUSH_MENU_STACK',
    payload: menuItem,
});

export const popMenuStack = (): Action => ({
    type: 'POP_MENU_STACK'
});

export const resetMenuStack = (): Action => ({
    type: 'RESET_MENU_STACK'
});

export type State = {
    content: React.ReactNode;
    menuPage: number;
    activeMenu: string;
    menuStack: string[];
};

export type Action =
    | {type: 'SET_CONTENT'; payload: React.ReactNode}
    | {type: 'SET_MENU_PAGE'; payload: number}
    | {type: 'SET_ACTIVE_MENU'; payload: string}
    | {type: 'PUSH_MENU_STACK'; payload: string}
    | {type: 'POP_MENU_STACK'}
    | {type: 'RESET_MENU_STACK'};
