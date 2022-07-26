import {NodeWrapper} from '../index';
import type {GroupProps} from '@givewp/forms/propTypes';
import {findNode} from '../../utilities/groups';
import Text from '../fields/Text';
import {Field} from '@givewp/forms/types';
import getErrorByFieldName from "../../utilities/getErrorByFieldName";
import {useFormState} from 'react-hook-form';

export default function Name({nodes, inputProps}: GroupProps) {
    const {errors} = useFormState();

    const firstName = findNode('firstName', nodes) as Field;
    const lastName = findNode('lastName', nodes) as Field | null;
    const honorific = findNode('honorific', nodes) as Field | null;

    const honorificError = getErrorByFieldName(errors, 'honorific');
    const firstNameError = getErrorByFieldName(errors, 'firstName');
    const lastNameError = getErrorByFieldName(errors, 'lastName');

    return (
        <>
            {honorific &&
                <NodeWrapper type={'text'} nodeType={'fields'} name={'honorific'}>
                    <Text inputProps={inputProps['honorific']}
                          fieldError={honorificError}
                          {...honorific}
                    />
                </NodeWrapper>
            }
            <NodeWrapper type={'text'} nodeType={'fields'} name={'firstName'}>
                <Text inputProps={inputProps['firstName']}
                      fieldError={firstNameError}
                      {...firstName}
                />
            </NodeWrapper>

            {lastName &&
                <NodeWrapper type={'text'} nodeType={'fields'} name={'lastName'}>
                    <Text inputProps={inputProps['lastName']}
                          fieldError={lastNameError}
                          {...lastName}
                    />
                </NodeWrapper>
            }
        </>
    );
}
