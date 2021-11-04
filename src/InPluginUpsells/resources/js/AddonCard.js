import styles from './AddonCard.module.css'

export const AddonCard = ({name, description, icon, image, features, actionLink, actionText}) => (
	<article className={styles.card}>
		<div className={styles.header}>
            <img src={icon} alt="" />
			<h3 className={styles.title}>{name}</h3>
		</div>
        <img className={styles.image} src={image} alt="" />
		<p className={styles.description}>{description}</p>
		<ul className={styles.features}>
			{features.map(feature => (
				<li key={feature} className={styles.feature}>
                    <svg className={styles.checkmark} viewBox="0 0 16 12" preserveAspectRatio="xMidYMid meet">
                        <use href="#give-in-plugin-upsells-checkmark" />
                    </svg>
                    {feature}
                </li>
			))}
		</ul>
		<a className={styles.button} href={actionLink}>
            {actionText}
        </a>
	</article>
);
