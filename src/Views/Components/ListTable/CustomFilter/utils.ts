/**
 * Default styles for React Select components in GiveWP Admin Table Filters
 * 
 * @unreleased
 */
export const defaultStyles = {
    option: (provided: any, state: any) => ({
        ...provided,
        backgroundColor: state.isSelected
            ? 'var(--givewp-neutral-100)'
            : state.isFocused
            ? 'var(--givewp-neutral-050, #f8f9fa)'
            : 'white',
        color: '#060c1a',
        cursor: 'pointer',
        whiteSpace: 'nowrap',
        overflow: 'hidden',
    }),
    clearIndicator: (p: any) => ({ ...p, display: 'none' }),
    indicatorSeparator: () => ({ display: 'none' }),
    dropdownIndicator: (p: any) => ({
        ...p,
        color: 'var(--givewp-neutral-500)',
        padding: '0 0.5rem',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        '&:hover': { color: 'var(--givewp-neutral-700)' },
        svg: { width: '14px', height: '14px' },
    }),
    input: (base: any) => ({
        ...base,
        outline: 'none',
        boxShadow: 'none',
        cursor: 'pointer',
    }),
    control: (base: any) => ({
        ...base,
        border: '1px solid #9ca0af',
        boxShadow: 'none',
        fontSize: '0.875rem',
        color: '#4b5563',
        outline: 'none',
        cursor: 'pointer',
        '&:hover': { borderColor: '#9ca0af' },
        ':focus-within': { boxShadow: 'none', outline: 'none' },
    }),
    menuPortal: (base) => ({ ...base, zIndex: 999, width: '100%' }),
    menu: (base) => ({ ...base, zIndex: 999, width: 'max-content', cursor: 'pointer'}),
    singleValue: (base: any) => ({ ...base, color: '#060c1a' }),
    placeholder: (base: any) => ({ ...base, color: '#9ca0af' }),
};

/**
 * Creates dynamic style configuration with optional width override
 * 
 * @param width - Optional width override for the control
 * @returns Style configuration object
 * 
 * @unreleased
 */
export function buildStyleConfig(width?: string | number) {
    return ({
        ...defaultStyles,
        control: (base: any) => ({
            ...defaultStyles.control(base),
            minWidth: width ? (typeof width === 'number' ? `${width}px` : width) : null,
        }),
    });
}
