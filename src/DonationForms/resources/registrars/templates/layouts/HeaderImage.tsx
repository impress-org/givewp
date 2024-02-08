import {HeaderImageProps} from '@givewp/forms/propTypes';

/**
 * @unreleased
 */
export default function HeaderImage({style, image, alt}: HeaderImageProps) {
    return (
        <div className={`givewp-layouts givewp-design-settings-image--${style}`}>
            <img src={image} alt={alt} />
        </div>
    );
}
