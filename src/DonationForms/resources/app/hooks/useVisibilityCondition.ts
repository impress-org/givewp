import {useMemo} from 'react';
import {useWatch} from 'react-hook-form';
import {FieldCondition} from '@givewp/forms/types';
import conditionOperatorFunctions from '@givewp/forms/app/utilities/conditionOperatorFunctions';

type WatchedFields = Map<string, any>;

/**
 * Adds visibility conditions to a field. The given conditions are watched and the hook returns true or false based on
 * whether the conditions are met.
 *
 * @since 3.0.0
 */
export default function useVisibilityCondition(conditions: FieldCondition[]): boolean {
    const watchedFieldNames = useMemo<string[]>(() => {
        if (!conditions.length) {
            return [];
        }

        return [...conditions.reduce(watchFieldsReducer, new Set()).values()];
    }, [conditions]);

    const fieldValues = useWatch({
        name: watchedFieldNames,
    });

    // useWatch returns a numeric array of values, so we need to map them to the field names.
    const watchedFields = useMemo<WatchedFields>(() => {
        return watchedFieldNames.reduce((fields, name, index) => {
            fields.set(name, fieldValues[index]);
            return fields;
        }, new Map());
    }, [watchedFieldNames, fieldValues]);

    return useMemo<boolean>(() => {
        if (!conditions.length) {
            return true;
        }

        return visibliityConditionsPass(conditions, watchedFields);
    }, [watchedFields]);
}

/**
 * Returns true or false based on whether the conditions are met.
 *
 * @since 3.0.0
 */
export function visibliityConditionsPass(conditions: FieldCondition[], watchedFields: WatchedFields): boolean {
    if (!conditions.length) {
        return true;
    }

    function conditionPassReducer(passing: boolean, condition: FieldCondition) {
        if (condition.type === 'basic') {
            const value = watchedFields.get(condition.field);

            const conditionPasses = conditionOperatorFunctions[condition.comparisonOperator](value, condition.value);

            return condition.logicalOperator === 'and'
                ? (passing ?? true) && conditionPasses
                : (passing ?? false) || conditionPasses;
        }

        return condition.boolean === 'and'
            ? (passing ?? true) && condition.conditions.reduce(conditionPassReducer, null)
            : (passing ?? false) || condition.conditions.reduce(conditionPassReducer, null);
    }

    return conditions.reduce(conditionPassReducer, null);
}

/**
 * A recursive reducer that returns a set of fields that are being watched by the conditions.
 *
 * @since 3.0.0
 */
function watchFieldsReducer(fields: Set<string>, condition: FieldCondition) {
    if (condition.type === 'basic') {
        return fields.add(condition.field);
    }

    return condition.conditions.reduce(watchFieldsReducer, fields);
}
