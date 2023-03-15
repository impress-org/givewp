export type StatusSelector = {
    options: {
        value: number;
        label: string;
        find(param: (option) => boolean);
    }
}
