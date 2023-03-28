export type SearchSelector = {
    options: Array<{
        value: number;
        label: string;
    }>;
    defaultLabel?: string;
    name: string;
    placeholder: string;
};
