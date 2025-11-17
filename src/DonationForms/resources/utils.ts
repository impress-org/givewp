import {useEntityRecord, useEntityRecords} from '@wordpress/core-data';
import {Form} from './types'


type FormStatus = "draft" | "publish" | "private" | "orphaned";

/**
 * @since 4.2.0
 */
export function useForm(formId: number) {
    const data = useEntityRecord('givewp', 'forms', formId);

    return {
        form: {
            ...data?.record as Form
        },
        hasResolved: data?.hasResolved,
        isResolving: data?.isResolving,
    };
}

type useFormsParams = {
    ids?: number[],
    page?: number,
    per_page?: number;
    status?: FormStatus[]
}

export function useForms({
     ids = [],
     page = 1,
     per_page = 30,
     status = ['publish']
}: useFormsParams = {}) {
    const data = useEntityRecords('givewp', 'form', {
        ids,
        page,
        per_page,
        status
    });

    return {
        forms: data?.records as Form[],
        //@ts-ignore
        totalItems: data.totalItems,
        //@ts-ignore
        totalPages: data.totalPages,
        hasResolved: data?.hasResolved,
        isResolving: data?.isResolving,
    };
}


