import { useState } from 'react';
import ReactSelect, { components } from 'react-select';
import { AsyncSelectEntity, useCampaignAsyncSelect } from './useAsyncCampaigns';
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
	asyncEntity?: AsyncSelectEntity;
}

/* Matches the menu's max-width in the stylesheet. */
const MENU_MAX_WIDTH_REM = 24;

/**
 * A menu opens from its select's left edge. When it would run past the right edge of the page, it opens from the
 * select's right edge instead.
 *
 * @since TBD
 */
function useMenuAlignment(inputId: string) {
    const [alignMenuEnd, setAlignMenuEnd] = useState(false);

    const onMenuOpen = () => {
        const root = document.documentElement;
        const select = document.getElementById(inputId)?.closest(`.${styles.searchableSelect}`);

        if (!select) {
            return;
        }

        const menuMaxWidth = MENU_MAX_WIDTH_REM * parseFloat(getComputedStyle(root).fontSize);

        setAlignMenuEnd(select.getBoundingClientRect().left + menuMaxWidth > root.clientWidth);
    };

    return {
        onMenuOpen,
        className: `${styles.searchableSelect} ${alignMenuEnd ? styles.alignMenuEnd : ''}`,
    };
}

/**
 * @since 4.10.0
 */
export default function CustomFilter(props: CustomFilterProps) {
	return props.isAsync ? <AsyncFilter {...props} /> : <DefaultFilter {...props} />
}

/**
 * @since TBD Keep the open menu inside the page.
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

	const inputId = `givewp-filter-${name}`;
	const menuAlignment = useMenuAlignment(inputId);

	return (
			<ReactSelect
				inputId={inputId}
				onMenuOpen={menuAlignment.onMenuOpen}
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
				className={menuAlignment.className}
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
 * @since TBD Load forms as well as campaigns through `asyncEntity`.
 * @since TBD Label the select for screen readers with `ariaLabel`.
 * @since TBD Pin any static `options` (for example "No campaign") above the async results.
 * @since TBD Keep the open menu inside the page.
 * @since 4.10.0
 */
function AsyncFilter({name, options = [], ariaLabel, placeholder, onChange, value, isSearchable, isClearable, asyncEntity}: CustomFilterProps) {
	const staticOptions = options.map(({value, text}) => ({value, label: text})) as unknown as CampaignOption[];
	const staticSelected = staticOptions.find((option) => String(option.value) === String(value)) ?? null;
	const { loadOptions, mapOptionsForMenu, selectedOption } = useCampaignAsyncSelect(
		staticSelected ? null : parseInt(value) || null,
		asyncEntity
	);

	const handleChange = (selectedOption: CampaignOption | null) => {
		onChange(name, selectedOption?.value.toString() ?? '');
	}

	const inputId = `givewp-async-filter-${name}`;
	const menuAlignment = useMenuAlignment(inputId);

	return (
		<AsyncPaginate
			inputId={inputId}
			onMenuOpen={menuAlignment.onMenuOpen}
			placeholder={placeholder}
			aria-label={ariaLabel}
			loadOptions={loadOptions}
			onChange={handleChange}
			value={staticSelected ?? selectedOption}
			isSearchable={isSearchable}
			isClearable={isClearable}
			mapOptionsForMenu={(loaded: CampaignOption[]) => [...staticOptions, ...mapOptionsForMenu(loaded)]}
			className={`${menuAlignment.className} ${styles.asyncSelect}`}
			classNamePrefix="searchableSelect"
			debounceTimeout={600}
		/>
	);
}
