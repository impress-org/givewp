import type {SectionProps} from '@givewp/forms/propTypes';

export default function Section({section: {name, label, description}, children}: SectionProps) {
    return (
        <fieldset aria-labelledby={name}>
            <div>
                <legend id={name}>{label}</legend>
                <p>
                    <em>{description}</em>
                </p>
            </div>
            <div className="givewp-section-nodes">{children}</div>
        </fieldset>
    );
}
