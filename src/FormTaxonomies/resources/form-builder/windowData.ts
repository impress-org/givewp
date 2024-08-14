type FormTagToken = {
    id: number;
    value: string;
};

interface TaxonomySettings {
    formTagsEnabled: boolean;
    formCategoriesEnabled: boolean;
    formTagsSelected: FormTagToken[];
    formCategoriesSelected: any[];
    formCategoriesAvailable: any[];
}

declare const window: {
    giveTaxonomySettings: TaxonomySettings;
} & Window;

export default function getWindowData(): TaxonomySettings {
    return window.giveTaxonomySettings;
}

export function isFormTagsEnabled(): boolean {
    return window.giveTaxonomySettings.formTagsEnabled;
}

export function isFormCategoriesEnabled(): boolean {
    return window.giveTaxonomySettings.formCategoriesEnabled;
}

export function getInitialFormTags(): FormTagToken[] {
    return window.giveTaxonomySettings.formTagsSelected;
}

export function getInitialFormCategories(): any[] {
    return window.giveTaxonomySettings.formCategoriesSelected;
}

export function getAvailableFormCategories(): any[] {
    return window.giveTaxonomySettings.formCategoriesAvailable;
}
