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
                    flex: 1,
                    display: 'flex',
                    gap: '0.5rem',
                    alignItems: 'center',
                    justifyContent: 'flex-start',
                    maxWidth: '33%',
                }}
            >
                {contentLeft}
            </section>
            <section style={{flex: 1, textAlign: 'center'}}>{contentMiddle}</section>
            <section
                style={{
                    flex: 1,
                    display: 'flex',
                    gap: '0.5rem',
                    alignItems: 'center',
                    justifyContent: 'flex-end',
                    maxWidth: '33%',
                }}
            >
                {contentRight}
            </section>
        </header>
    );
}
