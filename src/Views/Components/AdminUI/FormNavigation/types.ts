import {PageInformation} from '@givewp/components/AdminUI/FormPage/types';

export type FormNavigationProps = {
    onSubmit: () => void;
    pageInformation: PageInformation;
    actionConfig: Array<{title: string; action: any}>;
    isDirty: boolean;
};
