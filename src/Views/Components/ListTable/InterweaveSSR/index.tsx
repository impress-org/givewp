import {Interweave} from 'interweave';
import './styles.scss';

//@since 2.24.0 renders all SSR data from backend
const InterweaveSSR = ({column, item}) => {
    let value = item?.[column.id];
    if (value === undefined) {
        value = null;
    }

    if (!isNaN(value)) {
        return <div className={'idBadge'}>{value}</div>;
    }

    if (value === '' || value === null) {
        return <>'-'</>;
    }

    return <Interweave attributes={{className: 'interweave'}} content={value} />;
};
export default InterweaveSSR;
