export type FormNavigationProps = {
    navigationalOptions: Array<{id: number; title: string}>;
    onSubmit: () => void;
    pageDescription: string;
    pageId: number;
    pageTitle: string;
    actionConfig: Array<{title: string; action: any}>;
    isDirty: boolean;
}
