/**
 * @unreleased
 */
export default function HeaderImage({style, image, alt}) {
    return (
        <div className={`givewp-layouts givewp-design-settings-image--${style}`}>
            <img src={image} alt={alt} />
        </div>
    );
}
