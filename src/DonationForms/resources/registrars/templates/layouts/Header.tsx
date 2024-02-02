import type {HeaderProps} from '@givewp/forms/propTypes';

/**
 * @since 3.0.0
 */
export default function Header({TextWrapper, Title, Description, Goal}: HeaderProps) {
    return (
        <>
            <TextWrapper>
                <Title />
                <Description />
            </TextWrapper>
            <Goal />
        </>
    );
}
