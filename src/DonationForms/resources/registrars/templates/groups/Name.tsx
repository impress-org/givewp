import type {NameProps} from '@givewp/forms/propTypes';

export default function Name({
    nodeComponents: {honorific: Honorific, firstName: FirstName, lastName: LastName},
}: NameProps) {
    return (
        <>
            {Honorific && <Honorific />}
            <FirstName />
            <LastName />
        </>
    );
}
