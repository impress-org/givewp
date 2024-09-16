import {useForm} from 'react-hook-form';
import Joi from 'joi';
import {joiResolver} from '@hookform/resolvers/joi';
import {getValidationSchema} from '../../../../utils';

type FormInputs = {
    title: string;
    longDescription: string;
    image: string;
    goalType: string;
    goal: number;
};

type PropTypes = {
    defaultValues: FormInputs;
};

/**
 * @unreleased
 */
export default ({defaultValues}: PropTypes) => {

    const validationSchema = getValidationSchema({
        title: Joi.string().required(),
        longDescription: Joi.string(),
        image: Joi.string(),
        goalType: Joi.string().required(),
        goal: Joi.number().required(),
    });

    const {handleSubmit, control} = useForm<FormInputs>({
        defaultValues,
        resolver: joiResolver(validationSchema),
        reValidateMode: 'onBlur',
    });

    const onSubmit = (e: Event) => {
        e.preventDefault();
    };

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <div>
                Campaign settings
            </div>
        </form>
    )
}
