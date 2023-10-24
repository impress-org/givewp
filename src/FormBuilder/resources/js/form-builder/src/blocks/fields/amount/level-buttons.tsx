import cx from 'classnames';

const LevelButton = ({selected, children}) => {
    const classes = cx({
        'give-donation-block__level': true,
        'give-donation-block__level--selected': selected,
    });

    return <div className={classes}>{children}</div>;
};

export default LevelButton;
