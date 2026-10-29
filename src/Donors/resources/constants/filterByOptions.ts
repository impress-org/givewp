import {FilterByGroupedOptions} from '@givewp/components/ListTable/ListTablePage';
import {periodFilterGroup} from '@givewp/components/ListTable/adapters/periodFilterGroup';

const filterByOptions: Array<FilterByGroupedOptions> = [periodFilterGroup()];

export default filterByOptions;
