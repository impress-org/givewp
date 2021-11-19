const Footer = (props) => {
    const styles = {
        footer: {
            fontSize: '14px',
            color: '#767676',
            fontWeight: 600,
            borderTop: '1px solid #F2F2F2',
            backgroundColor: '#FBFBFB',
            borderRadius: '0 0 8px 8px',
        },
        footerStrong: {
            fontSize: '18px',
            color: '#4C4C4C',
        },
        footerLayout: {
            display: 'flex',
        },
    };

    return (
        <footer style={styles.footer}>
            <div style={styles.footerLayout}>{props.children}</div>
        </footer>
    );
};

const FooterItem = (props) => {
    const styles = {
        container: {
            flex: 1,
            padding: '15px',
            fontSize: '18px',
            textAlign: 'center',
            borderRadius: '0 0 8px 8px',
            lineHeight: '29px',
            border: '1px solid #F2F2F2',
            borderWidth: '0 1px',
        },
        strong: {
            fontSize: '24px',
            color: '#4C4C4C',
        },
    };
    return (
        <div style={styles.container}>
            <strong style={styles.strong}>{props.title}</strong>
            <br />
            {props.subtitle}
        </div>
    );
};

export {Footer, FooterItem};
