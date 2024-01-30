import {InspectorControls} from '@wordpress/block-editor';

import metadata from './block.json';
import Icon from './Icon';

import './styles.scss';

/**
 * @unreleased
 */
const settings = {
    ...metadata,
    icon: Icon,
    edit: ({attributes, setAttributes}) => {
        return (
            <>
               <div className={'givewp-event-tickets-block__placeholder'}>
                    Here goes the placeholder
                </div>

                <InspectorControls>
                    Here goes the settings
                </InspectorControls>
            </>
        );
    },
};

const eventTicketsBlock = {
    name: settings.name,
    settings,
};

export default eventTicketsBlock;
