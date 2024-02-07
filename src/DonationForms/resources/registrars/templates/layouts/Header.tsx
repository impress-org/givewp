import type {HeaderProps} from '@givewp/forms/propTypes';

/**
 * @since 3.0.0
 */
export default function Header({HeaderImage, Title, Description, Goal}: HeaderProps) {
    return (
        <>
            <Title />
            <Description />
            <HeaderImage />
            <Goal />
        </>
    );
}
