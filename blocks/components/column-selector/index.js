import {__} from '@wordpress/i18n';
import cx from 'classnames';
import './style.scss';

export function Selector({display, selected, onClick}) {
    const displayValue = String(display);

    const handleOnClick = () => {
        if (onClick instanceof Function) {
            onClick(displayValue);
        }
    };

    return (
        <div
            onClick={handleOnClick}
            className={cx(
                'give_column_selector_container',
                {'give_column_selector_selected': displayValue === selected}
            )}
        >
            {display === 'best-fit' ? (
                <div className="give_column_selector_icon_container">
                    <div>
                        {__('Best Fit (Responsive)', 'give')}
                    </div>
                    <div>
                        <div className="give_best_fit_icon">
                            <div className="give_best_fit_row">
                                <div className="give_best_fit_icon_box"> </div>
                                <div className="give_best_fit_icon_box"> </div>
                            </div>
                            <div className="give_best_fit_row">
                                <div className="give_best_fit_icon_box"> </div>
                            </div>
                        </div>
                    </div>
                </div>
            ) : (
                <>
                    {Array(display).fill(null).map((val, i) => <div key={i} className="give_column_selector_box"> </div>)}
                </>
            )}
        </div>
    )
}

export function Row({children}) {
    return (
        <div className="give_column_selector_row">
            {children}
        </div>
    )
}

export default function ({label, onClick, selected, help}) {
    return (
        <div className="give_column_selector">
            {label && (
                <p>{label}</p>
            )}

            {help && (
                <p className="give_column_selector_help_text">{help}</p>
            )}

            <Row>
                <Selector
                    display="best-fit"
                    onClick={onClick}
                    selected={selected}
                />
            </Row>

            <Row>
                <Selector
                    display={1}
                    onClick={onClick}
                    selected={selected}
                />
                <Selector
                    display={2}
                    onClick={onClick}
                    selected={selected}
                />
            </Row>

            <Row>
                <Selector
                    display={3}
                    onClick={onClick}
                    selected={selected}
                />
                <Selector
                    display={4}
                    onClick={onClick}
                    selected={selected}
                />
            </Row>
        </div>
    )
}
