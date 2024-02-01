import {plus, reset as minus} from '@wordpress/icons';
import {Icon} from '@wordpress/components';

/**
 * @unreleased
 */
export default function BlockPlaceholder({attributes}) {
    const classNamePrefix = 'givewp-event-tickets';
    return (
        <div className={`${classNamePrefix}-block__placeholder`}>
            <div className={`${classNamePrefix}`}>
                <div className={`${classNamePrefix}__header`}>
                    <div className={`${classNamePrefix}__header__date`}>
                        10 <span>Jan</span>
                    </div>
                    <h4 className={`${classNamePrefix}__header__title`}>Dinner Gala</h4>
                    <p className={`${classNamePrefix}__header__full-date`}>Wednesday, January 10th, 10am GMT</p>
                </div>
                <div className={`${classNamePrefix}__description`}>
                    <p>Description goes here and truncate when it exceeds two lines</p>
                </div>
                <div className={`${classNamePrefix}__tickets`}>
                    <h4>Select Tickets</h4>
                    <div className={`${classNamePrefix}__tickets__ticket`}>
                        <div className={`${classNamePrefix}__tickets__ticket__description`}>
                            <h5>Standard</h5>
                            <p>$50.00</p>
                            <p>Description goes here</p>
                        </div>
                        <div className={`${classNamePrefix}__tickets__ticket__quantity`}>
                            <div className={`${classNamePrefix}__tickets__ticket__quantity__input`}>
                                <button>
                                    <Icon icon={minus} />
                                </button>
                                <input type="text" value="0" />
                                <button>
                                    <Icon icon={plus} />
                                </button>
                            </div>
                            <p className={`${classNamePrefix}__tickets__ticket__quantity__availability`}>5 remaining</p>
                        </div>
                    </div>
                    <div className={`${classNamePrefix}__tickets__ticket`}>
                        <div className={`${classNamePrefix}__tickets__ticket__description`}>
                            <h5>VIP</h5>
                            <p>$100.00</p>
                            <p>Description goes here</p>
                        </div>
                        <div className={`${classNamePrefix}__tickets__ticket__quantity`}>
                            <div className={`${classNamePrefix}__tickets__ticket__quantity__input`}>
                                <button>
                                    <Icon icon={minus} />
                                </button>
                                <input type="text" value="0" />
                                <button>
                                    <Icon icon={plus} />
                                </button>
                            </div>
                            <p className={`${classNamePrefix}__tickets__ticket__quantity__availability`}>5 remaining</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
