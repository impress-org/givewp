import React, {useState} from 'react';
import {AddIcon, GiveIcon, ListIcon, SettingsIcon} from '../components/icons';
import {setFormSettings, useFormState, useFormStateDispatch} from '../stores/form-state';
import {RichText} from '@wordpress/block-editor';
import {Button} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {Header} from '../components';
import {Storage} from '../common';
import {FormSettings} from '@givewp/form-builder/types';
import {setIsDirty} from '@givewp/form-builder/stores/form-state/reducer';

const HeaderContainer = ({
    selectedSecondarySidebar,
    toggleSelectedSecondarySidebar,
    showSidebar,
    toggleShowSidebar,
    onSaveNotice,
}) => {
    const {blocks, settings: formSettings, isDirty} = useFormState();

    const {formTitle} = formSettings;
    const dispatch = useFormStateDispatch();
    const [isSaving, setSaving] = useState(false);

    const onSave = () => {
        setSaving(true);
        Storage.save({blocks, formSettings})
            .catch((error) => alert(error.message))
            .then(({pageSlug}: FormSettings) => {
                dispatch(setFormSettings({pageSlug}));
                dispatch(setIsDirty(false));
                setSaving(false);
                onSaveNotice();
            });
    };

    return (
        <Header
            contentLeft={
                <>
                    <div
                        style={{
                            height: '60px',
                            width: '60px',
                            backgroundColor: '#FFF',
                            display: 'flex',
                            alignItems: 'center',
                        }}
                    >
                        <div>
                            <a href={'edit.php?post_type=give_forms&page=give-forms'} title={'Return to GiveWP'}>
                                <GiveIcon />
                            </a>
                        </div>
                    </div>
                    <Button
                        onClick={() => toggleSelectedSecondarySidebar('add')}
                        isPressed={'add' === selectedSecondarySidebar}
                        icon={<AddIcon />}
                        variant={'primary'}
                    />
                    <Button
                        onClick={() => toggleSelectedSecondarySidebar('list')}
                        isPressed={'list' === selectedSecondarySidebar}
                        icon={<ListIcon />}
                    />
                </>
            }
            contentMiddle={
                <RichText
                    tagName="div"
                    value={formTitle}
                    onChange={(value) => dispatch(setFormSettings({formTitle: value}))}
                    style={{fontSize: '16px'}}
                />
            }
            contentRight={
                <>
                    <Button
                        onClick={onSave}
                        aria-disabled={isSaving || !isDirty}
                        disabled={isSaving || !isDirty}
                        variant="primary"
                    >
                        {isSaving ? __('Updating...', 'give') : __('Update', 'give')}
                    </Button>
                    <Button onClick={toggleShowSidebar} isPressed={showSidebar} icon={<SettingsIcon />} />
                </>
            }
        />
    );
};


export default HeaderContainer;
