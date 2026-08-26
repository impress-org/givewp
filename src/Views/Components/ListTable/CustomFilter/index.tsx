import ReactSelect, { components } from 'react-select';
import { useCampaignAsyncSelect } from './useAsyncCampaigns';
import { AsyncPaginate } from 'react-select-async-paginate';
import { CampaignOption } from './utils';
import styles from './styles.module.scss';

/**
 * @since 4.10.0
 */
type FilterOption = {
	value: string;
	text: string;
}

/**
 * @since 4.10.0
 */
type CustomFilterProps = {
	name: string;
	options?: FilterOption[];
	ariaLabel?: string;
	placeholder?: string;
	onChange: (name: string, value: string) => void;
	value?: string;
	isSearchable?: boolean;
	isSelectable?: boolean;
	isClearable?: boolean;
	isAsync?: boolean;
}

/**
 * @since 4.10.0
 */
export default function CustomFilter(props: CustomFilterProps) {
	return props.isAsync ? <AsyncFilter {...props} /> : <DefaultFilter {...props} />
}

/**
 * @since 4.10.0
 */
function DefaultFilter({name, options, ariaLabel, placeholder, onChange, value, isSearchable, isSelectable, isClearable}: CustomFilterProps) {
	const formattedOptions = options?.map(({ value, text }) => ({
		value,
		label: text,
	}));

	const valueOption = formattedOptions?.find((o) => o.value === value) || null;

	const handleChange = (selected: any) =>
		onChange(name, selected ? selected.value : '');

	const handleInputChange = (inputValue: string) => {
		onChange(name, inputValue);
	};

	return (
			<ReactSelect
				inputId={`givewp-filter-${name}`}
				name={name}
				options={formattedOptions}
				value={valueOption}
				onChange={handleChange}
				onInputChange={handleInputChange}
				placeholder={placeholder}
				aria-label={ariaLabel}
				isSearchable={isSearchable}
				isClearable={isClearable}
				classNamePrefix="searchableSelect"
				className={styles.searchableSelect}
				components={{
					DropdownIndicator: isSelectable ? components.DropdownIndicator : () => null,
					Menu: isSelectable ? components.Menu : () => null,
					MenuList: isSelectable ? components.MenuList : () => null,
					IndicatorSeparator: () => null,
					ClearIndicator: () => null,
				}}
			/>
	);
}

/**
 * @since 4.10.0
 */
function AsyncFilter({name, placeholder, onChange, value, isSearchable, isClearable}: CustomFilterProps) {
	const { loadOptions, mapOptionsForMenu, selectedOption } = useCampaignAsyncSelect(parseInt(value) || null);

	const handleChange = (selectedOption: CampaignOption | null) => {
		onChange(name, selectedOption?.value.toString() ?? '');
	}

	return (
		<AsyncPaginate
			inputId={`givewp-async-filter-${name}`}
			placeholder={placeholder}
			loadOptions={loadOptions}
			onChange={handleChange}
			value={selectedOption}
			isSearchable={isSearchable}
			isClearable={isClearable}
			mapOptionsForMenu={mapOptionsForMenu}
			className={`${styles.searchableSelect} ${styles.asyncSelect}`}
			classNamePrefix="searchableSelect"
			debounceTimeout={600}
		/>
	);
}
