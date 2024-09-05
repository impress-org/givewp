import {GiveCampaignDetails} from './types';
import styles from './CampaignDetailsPage.module.scss';

declare const window: {
    GiveCampaignDetails: GiveCampaignDetails;
} & Window;

export function getGiveCampaignDetailsWindowData() {
    return window.GiveCampaignDetails;
}

const {campaignDetailsPage} = getGiveCampaignDetailsWindowData();

console.log(Object.values(campaignDetailsPage.overviewTab));

export default function CampaignsDetailsPage() {
    return (
        <div className={styles.container}>
            <h1>
                <strong>Campaign details goes here...</strong>
            </h1>
            <p>Just below you can see a few data from the details page separated by tabs.</p>
            <br />
            <h2>
                <strong>Overview Tab Data</strong>
            </h2>
            <ul>
                {Object.entries(campaignDetailsPage.overviewTab).map(([property, value], index) => (
                    <li key={index}>
                        <span>
                            <strong>{property}:</strong> {String(value)}
                        </span>
                    </li>
                ))}
            </ul>
            <br />
            <h2>
                <strong>Settings Tab Data</strong>
            </h2>
            <p>
                <a
                    style={{fontSize: '1.5rem'}}
                    href={campaignDetailsPage.settingsTab.landingPageUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Campaign Landing Page ⭷
                </a>
            </p>
        </div>
    );
}
