/**
 * @since 3.4.0
 */
export default function HeaderTextWrapper({children}) {
    const {designSettingsImageStyle} = window.givewp.form.hooks.useDonationFormSettings();

    // @note The `cover` design setting requires the header title and description to be wrapped in a div.
    return 'cover' == designSettingsImageStyle
        ? <div className="givewp-layouts givewp-layouts-headerTextWrapper">{children}</div>
        : <>{children}</>
}
