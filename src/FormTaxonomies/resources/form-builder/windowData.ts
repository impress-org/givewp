/**
 * @since 3.16.0
 */
type FormTagToken = {
    id: number;
    value: string;
};

/**
 * @since 3.16.0
 */
type CategoryTerm = {
    id: number;
    name: string;
    parent: number;
};

/**
 * @since 3.16.0
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
 * @since 3.16.0
 */
export default function getWindowData(): TaxonomySettings {
    return window.giveTaxonomySettings;
}

/**
 * @since 3.16.0
 */
export function isFormTagsEnabled(): boolean {
    return window.giveTaxonomySettings.formTagsEnabled;
}

/**
 * @since 3.16.0
 */
export function isFormCategoriesEnabled(): boolean {
    return window.giveTaxonomySettings.formCategoriesEnabled;
}

/**
 * @since 3.16.0
 */
export function getInitialFormTags(): FormTagToken[] {
    return window.giveTaxonomySettings.formTagsSelected;
}

/**
 * @since 3.16.0
 */
export function getInitialFormCategories(): any[] {
    return window.giveTaxonomySettings.formCategoriesSelected;
}

/**
 * @since 3.16.0
 */
export function getAvailableFormCategories(): any[] {
    return window.giveTaxonomySettings.formCategoriesAvailable;
}
