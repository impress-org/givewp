import {FC} from 'react';

export interface HeaderProps {
    Title: FC;
    Description: FC;
    Goal: FC;
}

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
