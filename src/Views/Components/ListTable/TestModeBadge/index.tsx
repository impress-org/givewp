import './styles.scss';

type TestModeBadgeProps = {
    /** The badge text, from the screen's test-mode switch. */
    label: string;
};

// A flask. Decorative: the word next to it carries the meaning.
function FlaskIcon() {
    return (
        <svg
            className="givewp-test-mode-badge__icon"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
            focusable="false"
        >
            <path d="M9 3h6M10 3v6L4.5 19a1.5 1.5 0 0 0 1.3 2.2h12.4a1.5 1.5 0 0 0 1.3-2.2L14 9V3" />
        </svg>
    );
}

/**
 * The outline badge that shows while a screen is in test mode: a flask and the word.
 *
 * @since TBD
 */
export default function TestModeBadge({label}: TestModeBadgeProps) {
    return (
        <span className="givewp-test-mode-badge">
            <FlaskIcon />
            {label}
        </span>
    );
}
