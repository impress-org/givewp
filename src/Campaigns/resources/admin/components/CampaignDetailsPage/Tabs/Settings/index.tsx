import {useForm} from 'react-hook-form';
import Joi from 'joi';
import {joiResolver} from '@hookform/resolvers/joi';
import {getValidationSchema} from '../../../../utils';
import styles from '../../CampaignDetailsPage.module.scss';
import {__} from '@wordpress/i18n';
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

    const {handleSubmit, register, control} = useForm<FormInputs>({
        defaultValues,
        resolver: joiResolver(validationSchema),
        reValidateMode: 'onBlur',
    });

    const onSubmit = (e: Event) => {
        e.preventDefault();
    };

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <div className={styles.sections}>
                <div className={styles.section}>
                    <div>
                        <h2>
                            {__('Campaign Details', 'give')}
                        </h2>
                        {__('This includes the campaign title, description, and the cover of your campaign.', 'give')}
                    </div>
                    <div>
                        R
                    </div>
                </div>
            </div>
        </form>
    )
}
