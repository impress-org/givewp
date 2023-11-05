export default function SettingsSection({title, description = null, children}) {
    return (
        <div className={'givewp-form-settings__section'}>
            <div className={'givewp-form-settings__section__header'}>
                <h4>{title}</h4>
                {description && <p>{description}</p>}
            </div>
            <div className={'givewp-form-settings__section__body'}>{children}</div>
        </div>
    );
}
