import type {HeaderProps} from '@givewp/forms/propTypes';

/**
 * @since 3.0.0
 */
export default function Header({Title, Description, Goal}: HeaderProps) {
    return (
        <>
            <Title />
            <Description />
            <Goal />
        </>
    );
}
