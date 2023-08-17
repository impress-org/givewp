import type {HtmlProps} from '@givewp/forms/propTypes';

export default function Html({html}: HtmlProps) {
    return <div dangerouslySetInnerHTML={{__html: html}} />;
}
