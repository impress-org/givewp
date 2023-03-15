import {useState} from 'react';
import {__} from '@wordpress/i18n';

import Button from '@givewp/components/AdminUI/Button';
import ActionMenu from '@givewp/components/AdminUI/ActionMenu';
import LeftArrowIcon from '@givewp/components/AdminUI/FormNavigation/LeftArrowIcon';
import DownArrowIcon from '@givewp/components/AdminUI/FormNavigation/DownArrowIcon';

import {FormNavigationProps} from './types';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export default function FormNavigation({
    navigationalOptions,
    onSubmit,
    pageDescription,
    pageId,
    pageTitle,
    actionConfig,
    isDirty,
}: FormNavigationProps) {
    const [toggleActions, setToggleActions] = useState<boolean>(false);

    const toggleMoreActions = () => {
        setToggleActions(!toggleActions);
    };

    return (
        <header className={styles.formPageNavigation}>
            <div className={styles.wrapper}>
                <button className={styles.container} onClick={() => window.history.back()}>
                    <LeftArrowIcon />
                    <h1>{pageTitle}</h1>
                </button>

                <select>
                    {navigationalOptions?.map((option) => (
                        <option key={option.id}>{option.title}</option>
                    ))}
                </select>
            </div>

            <div className={styles.actions}>
                <div className={styles.pageDetails}>
                    <span>{pageDescription}:</span>
                    <span>#{pageId}</span>
                </div>

                <div className={styles.relativeContainer}>
                    <Button onClick={toggleMoreActions} variant={'secondary'} size={'small'} type={'button'}>
                        {__('More Actions', 'give')}
                        <DownArrowIcon />
                    </Button>
                    {toggleActions && <ActionMenu menuConfig={actionConfig} toggle={toggleMoreActions} />}
                </div>

                <Button onClick={onSubmit} variant={'primary'} size={'small'} type={'submit'} disabled={!isDirty}>
                    {__('Save Changes', 'give')}
                </Button>
            </div>
        </header>
    );
}
