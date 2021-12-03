const ProgressBar = (props) => {
    const styles = {
        wrapper: {
            padding: '5px',
        },
        container: {
            height: '20px',
            overflow: 'hidden',
            borderRadius: '14px',
            backgroundColor: '#F1F1F1',
            boxShadow: 'inset 0px 1px 4px rgba(0, 0, 0, 0.09487)',
        },
        progress: {
            width: props.percent + '%',
            height: 'inherit',
            borderRadius: 'inherit',
            background: props.color,
            backgroundBlendMode: 'multiply',
        },
    };

    return (
        <div style={styles.wrapper}>
            <div style={styles.container}>
                <div style={styles.progress}></div>
            </div>
        </div>
    );
};

export default ProgressBar;
