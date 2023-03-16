export type ActionMenuProps = {
    menuConfig: Array<{title: string; action: () => void}>;
    toggle: () => void;
};
