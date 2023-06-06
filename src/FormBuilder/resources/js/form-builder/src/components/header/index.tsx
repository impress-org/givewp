import {ReactNode} from 'react';

type Props = {
    contentLeft: ReactNode;
    contentMiddle: ReactNode;
    contentRight: ReactNode;
};

export default function Header({contentLeft, contentMiddle, contentRight}: Props) {
    return (
        <header
            style={{
                height: '60px',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                paddingLeft: '1rem',
                paddingRight: '1rem',
            }}
        >
            <section
                style={{
                    display: 'flex',
                    gap: '0.5rem',
                    alignItems: 'center',
                    justifyContent: 'flex-start',
                    flexBasis: '20%',
                }}
            >
                {contentLeft}
            </section>
            <section>{contentMiddle}</section>
            <section
                style={{
                    display: 'flex',
                    gap: '0.5rem',
                    alignItems: 'center',
                    flexBasis: '20%',
                    justifyContent: 'flex-end',
                }}
            >
                {contentRight}
            </section>
        </header>
    );
}
