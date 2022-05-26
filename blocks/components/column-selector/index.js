import {__} from '@wordpress/i18n';
import cx from 'classnames';
import './style.scss';

export function Selector({display, selected, onClick}) {
    const displayValue = String(display);

    return (
        <div
            className={cx(
                'give_column_selector_container',
                {'give_column_selector_selected': displayValue === selected}
            )}
        >
                <>
                    {Array(display).fill(null).map((val, i) => <div key={i} className="give_column_selector_box"> </div>)}
                </>
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

            {selected === '1' ? (
                    <Row>
                        <Selector
                            display={1}
                            onClick={onClick}
                            selected={selected}
                        />
                    </Row>
            ) : selected === '2' ? (
                <Row>
                    <Selector
                        display={2}
                        onClick={onClick}
                        selected={selected}
                    />
                </Row>
            ) : selected === '3' ? (
                <Row>
                    <Selector
                        display={3}
                        onClick={onClick}
                        selected={selected}
                     />
                </Row>
            ):
                <Row>
                    <Selector
                        display='4'
                        onClick={onClick}
                        selected={selected}
                    />
                </Row>
            }
        </div>
    )
}
