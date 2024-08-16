/**
 * @unreleased
 */
type FormTagToken = {
    id: number;
    value: string;
};

/**
 * @unreleased
 */
type CategoryTerm = {
    id: number;
    name: string;
    parent: number;
};

/**
 * @unreleased
 */
interface TaxonomySettings {
    formTagsEnabled: boolean;
    formTagsSelected: FormTagToken[];
    formCategoriesEnabled: boolean;
    formCategoriesSelected: number[];
    formCategoriesAvailable: CategoryTerm[];
}

declare const window: {
    giveTaxonomySettings: TaxonomySettings;
} & Window;

/**
 * @unreleased
 */
export default function getWindowData(): TaxonomySettings {
    return window.giveTaxonomySettings;
}

/**
 * @unreleased
 */
export function isFormTagsEnabled(): boolean {
    return window.giveTaxonomySettings.formTagsEnabled;
}

/**
 * @unreleased
 */
export function isFormCategoriesEnabled(): boolean {
    return window.giveTaxonomySettings.formCategoriesEnabled;
}

/**
 * @unreleased
 */
export function getInitialFormTags(): FormTagToken[] {
    return window.giveTaxonomySettings.formTagsSelected;
}

/**
 * @unreleased
 */
export function getInitialFormCategories(): any[] {
    return window.giveTaxonomySettings.formCategoriesSelected;
}

/**
 * @unreleased
 */
export function getAvailableFormCategories(): any[] {
    return window.giveTaxonomySettings.formCategoriesAvailable;
}
