export type SearchSelector = {
    options: Array<{
        value: number;
        label: string;
    }>;
    openSelector: boolean;
    setOpenSelector: React.Dispatch<React.SetStateAction<boolean>>;
};
