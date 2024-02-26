import {HeaderImageProps} from '@givewp/forms/propTypes';

/**
 * @unreleased
 */
export default function HeaderImage({url, alt}: HeaderImageProps) {
    return <img src={url} alt={alt} />;
}
