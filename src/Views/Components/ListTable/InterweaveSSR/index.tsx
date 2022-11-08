import {Interweave} from 'interweave';
import './styles.scss';
import {IdBadge} from '@givewp/components/ListTable/TableCell/TableCell';

//@unreleased renders all SSR data from backend
const InterweaveSSR = ({column, item}) => {
    let value = item?.[column.id];
    if (value === undefined) {
        value = null;
    }

    if (!isNaN(value)) {
        return <IdBadge id={value} />;
    }

    if (value === '' || value === null) {
        return <>'-'</>;
    }

    return <Interweave allowAttributes={true} attributes={{className: 'interweave'}} content={value} />;
};
export default InterweaveSSR;
