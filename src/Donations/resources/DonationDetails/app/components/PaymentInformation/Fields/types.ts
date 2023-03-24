export type FieldProps = {
    label: string;
    children: React.ReactNode;
    editable?: boolean;
    onEdit?: () => void;
};

export type CurrencyAmountDialogProps = {
    defaultAmount: number;
    amountChanged: (amount: number) => void;
};

export type CalendarProps = {
    closeCalendar: () => void;
};

export type TimeActionProps = {
    isOpen: boolean;
    closeFields: () => void;
    hours: number;
    minutes: number;
    ampm: string;
};

export type NumberFieldProps = {
    state: number;
    setState;
    label: string;
    id: string;
    min: number;
    max: number;
};

export type AmpmProps = {
    setState;
    state: string;
};
