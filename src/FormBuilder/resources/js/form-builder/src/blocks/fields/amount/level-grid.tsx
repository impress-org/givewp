import cx from 'classnames';

const LevelGrid = ({children, descriptionsEnabled}) => {
    const classes = cx({
        'give-donation-block__levels': true,
        'give-donation-block__levels--descriptions': descriptionsEnabled,
    });
    return <div className={classes}>{children}</div>;
};

export default LevelGrid;
