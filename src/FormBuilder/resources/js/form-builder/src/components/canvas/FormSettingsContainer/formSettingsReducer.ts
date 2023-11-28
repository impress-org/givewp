export const formSettingsReducer = (state: State, action: Action) => {
    switch (action.type) {
        case 'UPDATE_MENU_STATE':
            const {hasChildren, path} = action.payload;

            if (hasChildren) {
                return {
                    ...state,
                    menuPage: state.menuPage + 1,
                    activeMenu: path,
                };
            } else {
                return {
                    ...state,
                    activeMenu: path,
                    activeRoute: path,
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

export const updateMenuState = (hasChildren: boolean, path: string): Action => ({
    type: 'UPDATE_MENU_STATE',
    payload: {hasChildren, path},
});

export const navigateBackInMenu = (): Action => ({
    type: 'NAVIGATE_BACK_IN_MENU',
});

export type State = {
    menuPage: number;
    activeMenu: string;
    activeRoute: string;
};

export type MenuState = {
    hasChildren: boolean;
    path: string;
};

export type Action =
    | {type: 'UPDATE_MENU_STATE'; payload: MenuState}
    | {type: 'NAVIGATE_BACK_IN_MENU'};
