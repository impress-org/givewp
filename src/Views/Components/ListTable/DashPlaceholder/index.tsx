import './styles.scss';

interface DashPlaceholderProps {
    /** What is missing, for example "No value". Screen readers read it instead of the dash. */
    label: string;
}

/**
 * An empty value: a dash for sighted users and the label for screen readers. The markup matches
 * the PHP Give\Framework\ListTable\Views\Placeholder::dash(), which the same styles serve.
 *
 * @since TBD
 */
export default function DashPlaceholder({label}: DashPlaceholderProps) {
    return (
        <span className="givewp-list-table-dash">
            <span aria-hidden="true">—</span>
            <span className="screen-reader-text">{label}</span>
        </span>
    );
}
