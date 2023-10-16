import {useSelect} from '@wordpress/data';
import {getWindowData} from "@givewp/form-builder/common";

/**
 * @since 3.0.0
 */
const {disallowedFieldNames} = getWindowData();

/**
 * @since 3.0.0
 *
 * @returns {*}
 */
export const getFieldNameFrequency = (fieldName: string, fieldNames: string[]) => {
    return fieldNames.filter((name) => name === fieldName).length;
};

export const hasFieldNameConflict = (fieldName: string, fieldNames: string[]) => {
    return fieldNames.some((name) => name === fieldName);
};

/**
 * @since 3.0.0
 *
 * @returns {`${*}-${number|number}`}
 */
export const getFieldNameSuggestion = (name, names) => {
    const [prefix] = name.split(/^(.*)_([0-9]*)$/g).filter(Boolean);
    const similarFieldNames = names.filter((fieldName) => fieldName.startsWith(prefix));
    const increments = similarFieldNames.flatMap((fieldName) => fieldName.split(/^.*_([0-9]*)$/g).filter(Number)) || [
        0,
    ];
    const nextIncrement = increments.length ? Math.max(...increments) + 1 : 1;
    return `${prefix}_${nextIncrement}`;
};

/**
 * @since 3.0.0
 */
export const flattenBlocks = (block) => [block, ...block.innerBlocks.flatMap(flattenBlocks)];

const builtInFieldNames = [
    'amount',
    'currency',
    'gatewayId',
    'email',
    'company',
    'name',
    'firstName',
    'lastName',
    'honorific',
    'billingAddress',
    'country',
    'address1',
    'address2',
    'city',
    'state',
    'zip',
    'login',
    'donation-summary',
    'donationType',
    'subscriptionFrequency',
    'subscriptionInstallments',
    'subscriptionPeriod',
    'subscription_id',
    'additional_email',
    'formId',
    'comment',
];

/**
 * A hook for validating uniqueness of the 'fieldName' attribute.
 * When a conflict has been found, a new name suggestion will be generated and returned within the array
 *
 * @since 3.0.0
 *
 * @return {function(fieldName: string): [isUnique: boolean, suggestedName: string]}
 */
const useFieldNameValidator = () => {
    // @ts-ignore
    const blocks = useSelect((select) => select('core/block-editor').getBlocks(), []);

    const fieldNames = blocks
        .flatMap(flattenBlocks)
        .map((block) => block.attributes.fieldName)
        .filter((name) => name);

    /**
     * Returns a function that validates a given name against other field names.
     *
     * @param {string} n The name to validate
     * @param {boolean} allowOne Whether to allow a single instance of the name — useful for when a field name is being edited
     */
    return (n, allowOne = false): ValidationSet => {
        if (disallowedFieldNames.includes(n)) {
            return [false, getFieldNameSuggestion(n, fieldNames ?? [])];
        }

        const frequency = getFieldNameFrequency(n, fieldNames ?? []);
        const isUnique = allowOne ? frequency <= 1 : frequency === 0;

        return [isUnique, isUnique ? null : getFieldNameSuggestion(n, fieldNames ?? [])];
    };
};

type ValidationSet = [
    boolean, // validated name is unique
    string // suggested name if not unique
];

export default useFieldNameValidator;
