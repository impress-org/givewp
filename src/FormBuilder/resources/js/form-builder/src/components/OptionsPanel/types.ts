export interface OptionsPanelProps {
    currency: boolean;
    multiple: boolean;
    selectable?: boolean;
    options: OptionProps[];
    setOptions: (options: OptionProps[]) => void;
    defaultControlsTooltip?: string;
}

export interface OptionsListProps {
    currency: boolean;
    options: OptionProps[];
    showValues: boolean;
    multiple: boolean;
    selectable: boolean;
    setOptions: (options: OptionProps[]) => void;
    defaultControlsTooltip?: string;
}

export interface OptionsItemProps {
    currency: boolean;
    provided: any;
    option: OptionProps;
    showValues: boolean;
    multiple: boolean;
    selectable: boolean;
    defaultTooltip?: string;
    handleUpdateOptionLabel: (label: string) => void;
    handleUpdateOptionValue: (value: string) => void;
    handleUpdateOptionChecked: (checked: boolean) => void;
    handleRemoveOption: () => void;
}

export interface OptionProps {
    label: string;
    value: string;
    checked: boolean;
}
