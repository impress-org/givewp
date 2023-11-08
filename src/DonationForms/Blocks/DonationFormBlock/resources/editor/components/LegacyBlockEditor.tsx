import ServerSideRender from '@wordpress/server-side-render';
import Inspector from '../../../../../../../blocks/donation-form/edit/inspector';

export default function LegacyBlockEditor(props) {
    const {attributes} = props;
    return (
        <>
            <Inspector {...{...props}} />
            <ServerSideRender block="give/donation-form" attributes={attributes} />;
        </>
    );
}
