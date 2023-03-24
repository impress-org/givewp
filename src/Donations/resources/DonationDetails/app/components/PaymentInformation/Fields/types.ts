export type ActionContainerProps = {
    label: string;
    type: string;
    display: string | React.ReactNode;
    showEditDialog?: () => void;
    formField?: JSX.Element;
};

export type DonationMethodProps = {
    gateway: string;
    gatewayId: string;
};

export type DonationTypeProps = {
    donationType: 'single' | 'renewal' | 'subscription';
};

export type DatePickerProps = {
    setFocused: React.Dispatch<React.SetStateAction<boolean>>;
    handleFormField: (selectedDate) => void;
};

export type TimePickerProps = {
    showFormField: boolean;
    toggleFormField: () => void;
    parsedTime: Date;
    handleFormField: (hour, minute, ampm) => void;
};
