import FormTags from "./form-tags";

type FormTagToken = {
    id: number;
    value: string;
};

interface TaxonomySettings {
    formTagsEnabled: boolean;
    formCategoriesEnabled: boolean;
    formTags: FormTagToken[];
}

declare const window: {
    giveTaxonomySettings: TaxonomySettings;
} & Window;

export default function getWindowData(): TaxonomySettings {
    return window.giveTaxonomySettings;
}

export function getInitialFormTags(): FormTagToken[] {
    return window.giveTaxonomySettings.formTags;
}
