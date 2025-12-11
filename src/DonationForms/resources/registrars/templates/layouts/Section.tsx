import type {SectionProps} from '@givewp/forms/propTypes';

/**
 * @since 4.3.0 update legend to use the description prop.
 */
const Legend = ({name, description}: {name: string; description?: string}) => {
    return description.length > 0 ? (
        <div className="givewp-layouts-section__fieldset__legend">
            {description.length > 0 && <legend id={name}>{description}</legend>}
        </div>
    ) : (
        <></>
    );
};

/**
 * @since 4.3.0 use h3 tag for the section label.
 */
export default function Section({
    section: {name, label, description},
    hideLabel,
    hideDescription,
    children,
}: SectionProps) {
    return (
        <>
            {hideLabel ? '' : label.length > 0 && <h3 id={name} className="givewp-layouts-section__header">{label}</h3>}
            <fieldset className="givewp-layouts-section__fieldset" aria-labelledby={name}>
                <Legend
                    name={name}
                    description={hideDescription ? '' : description}
                />
                <div className="givewp-section-nodes">{children}</div>
            </fieldset>
        </>
    );
}
