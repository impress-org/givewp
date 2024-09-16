import {useForm} from 'react-hook-form';
import Joi from 'joi';
import {joiResolver} from '@hookform/resolvers/joi';
import {getValidationSchema} from '../../../../utils';

type FormInputs = {
    campaignTitle: string;
    longDescription: string;
    campaignImage: string;
    campaignGoalType: string;
    campaignGoal: number;
};

type PropTypes = {
    defaultValues: FormInputs;
};

/**
 * @unreleased
 */
export default ({defaultValues}: PropTypes) => {

    const validationSchema = getValidationSchema({
        campaignTitle: Joi.string().required(),
        longDescription: Joi.string(),
        campaignImage: Joi.string(),
        campaignGoalType: Joi.string().required(),
        campaignGoal: Joi.number().required(),
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

            </div>
        </form>
    )
}
