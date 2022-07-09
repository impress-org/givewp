import {GroupProps} from '../index';
import {findNode} from '../../utilities/groups';
import Text from '../fields/Text';
import {Field} from '@givewp/forms/types';
import {useFormContext} from "react-hook-form";
import getErrorByFieldName from "../../utilities/getErrorByFieldName";

export default function Name({nodes, inputProps}: GroupProps) {
    const firstName = findNode('firstName', nodes) as Field;
    const lastName = findNode('lastName', nodes) as Field | null;
    const honorific = findNode('honorific', nodes) as Field | null;
    const {formState: {errors}} = useFormContext();

    return (
        <>
            {honorific && <Text inputProps={inputProps['honorific']}
                                fieldError={getErrorByFieldName(errors, 'honorific')}
                                {...honorific}
            />}
            <Text inputProps={inputProps['firstName']}
                  fieldError={getErrorByFieldName(errors, 'firstName')}
                  {...firstName}
            />
            {lastName && <Text inputProps={inputProps['lastName']}
                               fieldError={getErrorByFieldName(errors, 'lastName')}
                               {...lastName}
            />}
        </>
    );
}
