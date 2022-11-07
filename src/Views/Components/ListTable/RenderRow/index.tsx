import {Interweave} from 'interweave';
import './styles.scss';

const RenderRow = ({column, item}) => {
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

    return <Interweave allowAttributes={true} attributes={{className: 'interweave'}} content={value} />;
};
export default RenderRow;
