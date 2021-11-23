/**
 * WordPress dependencies
 */
const {Fragment} = wp.element;
const ServerSideRender = wp.serverSideRender;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

const edit = ({attributes, setAttributes}) => {
    return (
        <Fragment>
            <Inspector {...{attributes, setAttributes}} />
            <ServerSideRender block="give/donor-dashboard" attributes={attributes} />
        </Fragment>
    );
};
export default edit;
