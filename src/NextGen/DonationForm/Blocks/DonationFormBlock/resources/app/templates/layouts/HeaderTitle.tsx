export interface HeaderTitleProps {
    text: string;
}

/**
 * @unreleased
 */
export default function HeaderTitle({text}: HeaderTitleProps) {
    return <h2>{text}</h2>;
}
