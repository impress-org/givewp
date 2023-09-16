import cx from 'classnames';

const LevelButton = ({selected, children}) => {
    const classes = cx({
        'give-level-button': true,
        'give-level-button-selected': selected,
    });

    return (
        <div className={classes} style={styles}>
            {children}
        </div>
    );
};

export default LevelButton;
