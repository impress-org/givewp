// Action type constants
const UPDATE_MENU_STATE = "UPDATE_MENU_STATE";
const NAVIGATE_BACK_IN_MENU = "NAVIGATE_BACK_IN_MENU";

/**
 * @unreleased
 */
export const formSettingsReducer = (state: State, action: Action) => {
    switch (action.type) {
        case UPDATE_MENU_STATE:
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
        case NAVIGATE_BACK_IN_MENU:
            return {
                ...state,
                menuPage: state.menuPage - 1,
            };
        default:
            return state;
    }
}

/**
 * @unreleased
 */
export const updateMenuState = (hasChildren: boolean, path: string): Action => ({
    type: UPDATE_MENU_STATE,
    payload: {hasChildren, path},
});

/**
 * @unreleased
 */
export const navigateBackInMenu = (): Action => ({
    type: NAVIGATE_BACK_IN_MENU,
});

/**
 * @unreleased
 */
export type State = {
    menuPage: number;
    activeMenu: string;
    activeRoute: string;
};

/**
 * @unreleased
 */
export type MenuState = {
    hasChildren: boolean;
    path: string;
};

/**
 * @unreleased
 */
export type Action = {type: typeof UPDATE_MENU_STATE; payload: MenuState} | {type: typeof NAVIGATE_BACK_IN_MENU};
