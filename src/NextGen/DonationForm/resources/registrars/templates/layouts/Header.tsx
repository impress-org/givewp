import type {HeaderProps} from '@givewp/forms/propTypes';

/**
 * @unreleased
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
