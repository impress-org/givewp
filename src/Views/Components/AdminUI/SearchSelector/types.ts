export type SearchSelector = {
    options: Array<{
        value: number;
        label: string;
    }>;
    defaultLabel: string;
    openSelector: boolean;
    setOpenSelector: React.Dispatch<React.SetStateAction<boolean>>;
};
