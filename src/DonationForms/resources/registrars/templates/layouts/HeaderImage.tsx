import {HeaderImageProps} from '@givewp/forms/propTypes';

/**
 * @unreleased
 */
export default function HeaderImage({url, alt, color, opacity}: HeaderImageProps) {
    // @ts-ignore
    return (
        <>
            {color && (
                <div
                    style={{'--givewp-image-color': color, '--givewp-image-opacity': opacity}}
                    className={'givewp-layouts-headerImage__overlay'}
                />
            )}
            <img src={url} alt={alt} />
        </>
    );
}
