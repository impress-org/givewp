import Notice from '@givewp/admin/components/Notices';
import {AdminSectionField} from '@givewp/components/AdminDetailsPage/AdminSection';
import {useEffect, useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import {useFormContext, useFormState} from 'react-hook-form';
import {EditIcon} from './Icons';
import styles from './styles.module.scss';

interface LockedTextInputProps {
    name: string;
    label: string;
    description: string;
    placeholder?: string;
    warningMessage: string;
}

/**
 * @since 4.11.0
 */
export default function LockedTextInput({name, label, description, placeholder, warningMessage}: LockedTextInputProps) {
    const {register, setFocus} = useFormContext();
    const {errors, isSubmitSuccessful} = useFormState();
    const [isEditing, setIsEditing] = useState(false);

    const handleEditClick = () => {
        const newEditingState = !isEditing;
        setIsEditing(newEditingState);

        // Focus on input when editing is activated
        if (newEditingState) {
            setFocus(name);
        }
    };

    // Reset editing mode when form is successfully submitted
    useEffect(() => {
        if (isSubmitSuccessful) {
            setIsEditing(false);
        }
    }, [isSubmitSuccessful]);

    return (
        <AdminSectionField error={errors[name]?.message as string}>
            <label htmlFor={name}>{label}</label>
            <p>{description}</p>
            <div className={styles.inputContainer}>
                <input
                    id={name}
                    type="text"
                    className={styles.input}
                    {...register(name)}
                    placeholder={placeholder}
                    readOnly={!isEditing}
                />
                <button
                    type="button"
                    className={`${styles.editButton} ${isEditing ? styles.editing : ''}`}
                    onClick={handleEditClick}
                    aria-label={isEditing ? __('Cancel editing', 'give') : __('Edit field', 'give')}
                >
                    <EditIcon strokeColor={isEditing ? '#9ca0af' : '#000'} />
                </button>
            </div>
            {isEditing && <Notice type="warning">{warningMessage}</Notice>}
        </AdminSectionField>
    );
}
