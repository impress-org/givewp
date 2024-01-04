export default function HeaderTextWrapper({children}) {
    const {designSettingsImageStyle} = window.givewp.form.hooks.useDonationFormSettings();

    return 'cover' == designSettingsImageStyle
        ? <div className="givewp-layouts givewp-layouts-headerTextWrapper">{children}</div>
        : <>{children}</>
}
