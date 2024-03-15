import {HeaderImageProps} from '@givewp/forms/propTypes';

/**
 * @since 3.5.0
 */
export default function HeaderImage({url, alt, color, opacity}: HeaderImageProps) {
    return (
        <>
            {color && (
                <div
                    style={{'--givewp-image-color': color, '--givewp-image-opacity': opacity} as React.CSSProperties}
                    className={'givewp-layouts-headerImage__overlay'}
                />
            )}
            <img src={url} alt={alt} />
        </>
    );
}
