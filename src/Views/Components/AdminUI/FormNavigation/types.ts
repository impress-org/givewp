export type FormNavigationProps = {
    navigationalOptions: Array<{id: number; title: string}>;
    onSubmit: () => void;
    pageInformation: {
        description: string;
        id: number;
        title: string;
    };
    actionConfig: Array<{title: string; action: any}>;
    isDirty: boolean;
};
