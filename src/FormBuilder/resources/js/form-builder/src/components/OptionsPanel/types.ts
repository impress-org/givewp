export interface OptionsPanelProps {
    currency: boolean;
    multiple: boolean;
    selectable?: boolean;
    options: OptionProps[];
    defaultControlsTooltip?: string;
    setOptions: (options: OptionProps[]) => void;
    onRemoveOption?: (option: OptionProps, index: number) => void;
    onAddOption?: () => void;
}

export interface OptionsListProps {
    currency: boolean;
    options: OptionProps[];
    showValues: boolean;
    multiple: boolean;
    selectable: boolean;
    defaultControlsTooltip?: string;
    setOptions: (options: OptionProps[]) => void;
    onRemoveOption?: (option: OptionProps, index: number) => void;
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
    id?: string;
    label: string;
    value: string;
    checked: boolean;
}
