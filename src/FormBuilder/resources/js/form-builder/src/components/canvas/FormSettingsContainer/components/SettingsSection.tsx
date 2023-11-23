export default function SettingsSection({title, description = null, children}) {
    return (
        <div className={'givewp-form-settings__section'}>
            <h4>{title}</h4>
            <p>{description}</p>
            {children}
        </div>
    );
}
