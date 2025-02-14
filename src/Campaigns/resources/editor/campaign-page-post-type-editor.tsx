import { close } from '@wordpress/icons';
import { registerPlugin } from '@wordpress/plugins';
import {
    __experimentalFullscreenModeClose as FullscreenModeClose,
    __experimentalMainDashboardButton as MainDashboardButton,
    // @ts-ignore
} from '@wordpress/edit-post';

declare const window: {
    giveCampaignPage: {
        campaignDetailsURL: string;
    };
} & Window;

registerPlugin( 'campaign-page-editor-back-button', {
    render: () =>  (
        <MainDashboardButton>
            <FullscreenModeClose
                icon={ close }
                href={window.giveCampaignPage.campaignDetailsURL}
                showTooltip={false} // Note: There is not a prop to customize the tooltip text, so we hide it.
            />
        </MainDashboardButton>
    )
} );
