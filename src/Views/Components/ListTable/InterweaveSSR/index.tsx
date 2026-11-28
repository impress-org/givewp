import {Interweave} from 'interweave';
import {__} from '@wordpress/i18n';
import DashPlaceholder from '../DashPlaceholder';
import './styles.scss';

/**
 * Renders all SSR data from backend.
 *
 * @since TBD Show the labeled dash for an empty value.
 * @since 2.24.0
 */
const InterweaveSSR = ({column, item}) => {
    let value = item?.[column.id];
    if (value === undefined) {
        value = null;
    }

    if (column.id === 'id') {
        return <div className={'idBadge'}>{value}</div>;
    }

    if (value === '' || value === null) {
        return <DashPlaceholder label={__('No value', 'give')} />;
    }

    return <Interweave attributes={{className: 'interweave'}} content={value} />;
};
export default InterweaveSSR;
