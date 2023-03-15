export type SearchSelector = {
    options: {
        value: number;
        label: string
    }
    openSelector: boolean;
    setOpenSelector: React.Dispatch<React.SetStateAction<boolean>>;
}
