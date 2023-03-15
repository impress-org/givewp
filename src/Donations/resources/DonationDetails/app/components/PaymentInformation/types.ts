export type ActionContainerProps = {
    label: string;
    type: string;
    value: string | React.ReactNode;
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
    setFocused;
    handleDateChange;
};

export type TimePickerProps = {};
