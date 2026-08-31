import {RequestUtils} from '@wordpress/e2e-test-utils-playwright';

/**
 * Reads and writes a v3 form through the route the form builder itself saves over.
 *
 * The builder is the only way a person edits a form, but driving it to set up every variation a
 * donor-facing spec needs would test the builder over and over on the way to testing something
 * else. These go straight to `givewp/v3/form/<id>`, which is the same round trip the builder makes,
 * so a form built here is a form the builder could have built.
 */

export type FormBlock = {
    clientId?: string;
    name: string;
    isValid?: boolean;
    attributes?: Record<string, unknown>;
    innerBlocks?: FormBlock[];
};

export type FormDefinition = {blocks: FormBlock[]; settings: Record<string, any>};

/**
 * The route hands back blocks and settings as JSON strings and takes them back the same way.
 */
export async function readForm(requestUtils: RequestUtils, formId: number): Promise<FormDefinition> {
    const response = await requestUtils.rest<{blocks: string; settings: string}>({
        path: `/givewp/v3/form/${formId}`,
    });

    return {blocks: JSON.parse(response.blocks), settings: JSON.parse(response.settings)};
}

export async function writeForm(requestUtils: RequestUtils, formId: number, form: FormDefinition): Promise<void> {
    await requestUtils.rest({
        method: 'POST',
        path: `/givewp/v3/form/${formId}`,
        data: {blocks: JSON.stringify(form.blocks), settings: JSON.stringify(form.settings)},
    });
}

/**
 * Applies `edit` to the form and saves the result.
 */
export async function editForm(
    requestUtils: RequestUtils,
    formId: number,
    edit: (form: FormDefinition) => void
): Promise<void> {
    const form = await readForm(requestUtils, formId);

    edit(form);

    await writeForm(requestUtils, formId, form);
}

/**
 * The section a block sits in, by the title the default form gives it.
 *
 * Every field lives inside a `givewp/section`, and which section a field is in decides which step
 * of a multi-step form it appears on, so tests that add a field have to say where it goes.
 */
export function section(form: FormDefinition, title: string): FormBlock {
    const found = form.blocks.find((block) => block.attributes?.title === title);

    if (!found) {
        throw new Error(`No section titled "${title}" on this form. Sections: ${sectionTitles(form).join(', ')}`);
    }

    return found;
}

function sectionTitles(form: FormDefinition): string[] {
    return form.blocks.map((block) => String(block.attributes?.title ?? block.name));
}
