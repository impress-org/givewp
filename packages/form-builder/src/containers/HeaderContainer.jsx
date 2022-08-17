import React, {useState} from "react";
import {AddIcon, GiveIcon, ListIcon, SettingsIcon} from "../components/icons";
import {useFormSettings} from "../settings/context";
import {RichText} from "@wordpress/block-editor";
import {Button} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {Header} from '../components';

const HeaderContainer = ({
                             saveCallback,
                             selectedSecondarySidebar,
                             toggleSelectedSecondarySidebar,
                             showSidebar,
                             toggleShowSidebar,
                         }) => {

    const [{formTitle}, updateFormSetting] = useFormSettings();

    const [isSaving, setSaving] = useState(false);

    const onSave = () => {
        setSaving(true);
        saveCallback().finally(() => {
            setSaving(false);
        });
    };

    return (
        <Header
            contentLeft={
                <>
                    <div style={{
                        height: '60px',
                        width: '60px',
                        backgroundColor: '#FFF',
                        display: 'flex',
                        alignItems: 'center',
                    }}>
                        <div>
                            <a href={'edit.php?post_type=give_forms&page=give-forms'} title={'Return to GiveWP'}>
                                <GiveIcon />
                            </a>
                        </div>
                    </div>
                    <Button onClick={() => toggleSelectedSecondarySidebar('add')}
                            isPressed={'add' === selectedSecondarySidebar} icon={<AddIcon />}
                            variant={'primary'} />
                    <Button onClick={() => toggleSelectedSecondarySidebar('list')}
                            isPressed={'list' === selectedSecondarySidebar} icon={<ListIcon />}
                    />
                </>
            }
            contentMiddle={
                <RichText
                    tagName="div"
                    value={formTitle}
                    onChange={(value) => updateFormSetting({formTitle: value})}
                    style={{fontSize: '16px'}}
                />
            }
            contentRight={
                <>
                    <Button onClick={onSave} disabled={isSaving} variant="primary">
                        {isSaving ? __('Publishing...', 'give') : __('Publish', 'give')}
                    </Button>
                    <Button onClick={toggleShowSidebar} isPressed={showSidebar} icon={<SettingsIcon />} />
                </>
            }
        />
    );
};


export default HeaderContainer;
