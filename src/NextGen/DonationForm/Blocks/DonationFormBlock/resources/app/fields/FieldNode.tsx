import buildRegisterValidationOptions from "../utilities/buildRegisterValidationOptions";
import {Field} from '@givewp/forms/types';
import {getFieldTemplate} from '../templates';
import getErrorByFieldName from "../utilities/getErrorByFieldName";
import {useFormContext, useFormState} from "react-hook-form";
import {useMemo} from "react";

export default function FieldNode({node}: { node: Field }) {
    const {register} = useFormContext();
    const {errors} = useFormState();
    const Field = useMemo(() => getFieldTemplate(node.type), [node.type]);
    const inputProps = register(node.name, buildRegisterValidationOptions(node.validationRules));

    return <Field key={node.name} inputProps={inputProps}
                  placeholder={node.placeholder}
                  fieldError={getErrorByFieldName(errors, node.name)}
                  {...node} />;
}

