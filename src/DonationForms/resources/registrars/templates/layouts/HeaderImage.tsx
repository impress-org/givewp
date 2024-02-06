/**
 * @unreleased
 */
export default function HeaderImage({style, image}) {
    return (
        <div className={`givewp-layouts givewp-design-settings-image--${style}`}>
            <img src={image} alt={''} />
        </div>
    );
}
