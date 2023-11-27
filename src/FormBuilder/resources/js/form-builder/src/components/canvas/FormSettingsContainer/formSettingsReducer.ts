export const formSettingsReducer = (state: State, action: Action) => {
    switch (action.type) {
        case 'SET_CONTENT':
            return {...state, content: action.payload};
        case 'UPDATE_MENU_STATE':
            const {hasNestedMenu, menuItem, children} = action.payload;

            if (hasNestedMenu) {
                return {
                    ...state,
                    menuPage: state.menuPage + 1,
                };
            } else {
                return {
                    ...state,
                    activeMenu: menuItem,
                    content: children,
                };
            }
        case 'NAVIGATE_BACK_IN_MENU':
            return {
                ...state,
                menuPage: state.menuPage - 1,
            };
        default:
            return state;
    }
}

export const setContent = (content: React.ReactNode): Action => ({
    type: 'SET_CONTENT',
    payload: content,
});

export const updateMenuState = (hasNestedMenu: boolean, menuItem: string, children: React.ReactNode): Action => ({
    type: 'UPDATE_MENU_STATE',
    payload: {hasNestedMenu, menuItem, children},
});

export const navigateBackInMenu = (): Action => ({
    type: 'NAVIGATE_BACK_IN_MENU',
});

export type State = {
    content: React.ReactNode;
    menuPage: number;
    activeMenu: string;
};

export type MenuState = {
    hasNestedMenu: boolean;
    menuItem: string;
    children: React.ReactNode;
};

export type Action =
    | {type: 'SET_CONTENT'; payload: React.ReactNode}
    | {type: 'UPDATE_MENU_STATE'; payload: MenuState}
    | {type: 'NAVIGATE_BACK_IN_MENU'};
