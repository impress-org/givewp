import './styles.scss';
import {Interweave} from 'interweave';

const RenderRow = ({item}) => {
    return <Interweave allowAttributes={true} attributes={{className: 'interweave'}} content={item} />;
};
export default RenderRow;
