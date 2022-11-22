export interface HeaderDescriptionProps {
    text: string;
}

/**
 * @unreleased
 */
export default function HeaderDescription({text}: HeaderDescriptionProps) {
    return <p>{text}</p>
}
