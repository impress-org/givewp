import {useMemo} from 'react';
import {kebabCase} from 'lodash';
import {Button} from './Button';
import {Card} from './Card';
import {transformEmphasis, transformStrong} from '../utils';

import styles from './PricingPlanCard.module.css';

export const PricingPlanCard = ({
    name,
    description,
    actionText,
    actionLink,
    icon,
    includes,
    savingsPercentage,
    isMostPopular,
}) => {
    const includesLabelId = useMemo(() => `${kebabCase(name)}-includes-label`, [name]);

    return (
        <Card as="article" className={styles.card}>
            <div>
                <div className={styles.nameAndFlag}>
                    <h3 className={styles.title}>
                        <img className={styles.icon} src={icon} alt="" />
                        {name}
                    </h3>
                    {isMostPopular && <div className={styles.mostPopularFlag}>Most Popular</div>}
                </div>
                <p className={styles.description} dangerouslySetInnerHTML={{__html: transformStrong(description)}} />
                <div className={styles.actionAndSavings}>
                    <Button as="a" href={actionLink} rel="noopener" target="_blank" className={styles.button}>
                        {actionText}
                    </Button>
                    <p className={styles.savings}>Save over {savingsPercentage}%</p>
                </div>
            </div>
            <aside aria-labelledby={includesLabelId} className={styles.includes}>
                <h4 id={includesLabelId} className={styles.includesLabel}>
                    <span className="screen-reader-text">{name} </span>Includes
                </h4>
                <ul className={styles.includesList}>
                    {includes.map((include) => (
                        <li key={include.feature} className={styles.include}>
                            {include.icon && <img src={include.icon} alt="" className={styles.includeIcon} />}
                            {include.link ? (
                                <a
                                    href={include.link}
                                    target="_blank"
                                    rel="noopener"
                                    dangerouslySetInnerHTML={{__html: transformEmphasis(include.feature)}}
                                />
                            ) : (
                                <span dangerouslySetInnerHTML={{__html: transformEmphasis(include.feature)}} />
                            )}
                        </li>
                    ))}
                </ul>
            </aside>
        </Card>
    );
};
