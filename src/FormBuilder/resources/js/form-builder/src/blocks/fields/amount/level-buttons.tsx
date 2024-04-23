import cx from 'classnames';

const LevelButton = ({selected, children, descriptionsEnabled}) => {
    const classes = cx({
        'give-donation-block__level': true,
        'give-donation-block__level--selected': selected,
        'give-donation-block__level--descriptions': descriptionsEnabled,
    });

    return <div className={classes}>{children}</div>;
};

export default LevelButton;
