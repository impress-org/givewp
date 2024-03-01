import {HeaderImageProps} from '@givewp/forms/propTypes';

/**
 * @since 3.5.0
 */
export default function HeaderImage({url, alt}: HeaderImageProps) {
    return <img src={url} alt={alt} />;
}
