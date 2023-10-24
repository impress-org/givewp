interface Option {
    /**
     * The label to be shown to the user.
     */
    label: string;
    /**
     * The internal value used to choose the selected value. This is also
     * the value passed to `onChange` when the option is selected.
     */
    value: string;

    /**
     * Disabled attribute on option element
     */
    disabled?: boolean;
}

export type {
    Option
}
