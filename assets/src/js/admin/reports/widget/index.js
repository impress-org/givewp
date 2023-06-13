// Reports admin dashboard widget

// Dependencies
import {__} from '@wordpress/i18n';

// Store-related dependencies
import {useStoreValue} from '../store';

import './style.scss';

// Components
import Grid from '../components/grid';
import Card from '../components/card';
import RESTChart from '../components/rest-chart';
import RESTMiniChart from '../components/rest-mini-chart';
import NoDataNotice from '../components/no-data-notice';
import LoadingNotice from '../components/loading-notice';
import MiniPeriodSelector from '../components/mini-period-selector';

const Widget = () => {
    const [{giveStatus, pageLoaded}] = useStoreValue();

    return (
        <div className="givewp-reports-widget-container">
            <RecurringAddonOverlay />
            {giveStatus === 'no_donations_found' && <NoDataNotice version={'dashboard'} />}
            {pageLoaded === false && <LoadingNotice />}
            <Grid gap="12px" visible={pageLoaded}>
                <Card width={12}>
                    <RESTChart
                        title={__('Overview', 'give')}
                        headerElements={<MiniPeriodSelector />}
                        type="line"
                        aspectRatio={0.8}
                        endpoint="income"
                        showLegend={false}
                    />
                </Card>
                <Card width={6}>
                    <RESTMiniChart title={__('Total Revenue', 'give')} endpoint="total-income" />
                </Card>
                <Card width={6}>
                    <RESTMiniChart title={__('Avg. Donation', 'give')} endpoint="average-donation" />
                </Card>
                <Card width={6}>
                    <RESTMiniChart title={__('Total Donors', 'give')} endpoint="total-donors" />
                </Card>
                <Card width={6}>
                    <RESTMiniChart title={__('Total Refunds', 'give')} endpoint="total-refunds" />
                </Card>
            </Grid>
        </div>
    );
};

function RecurringAddonOverlay() {
    const [{assets}] = useStoreValue();

    return (
        <div className={'givewp-reports-widget-overlay'}>
            <div className={'givewp-reports-widget-overlay-content'}>
                <h3 className={'givewp-reports-widget-overlay-content-title'}>
                    {' '}
                    {__('Get a quick view of your donation activity', 'give')}
                </h3>
                <p>{__("You don't have any donations yet", 'give')}</p>
                <p>
                    {__(
                        'Boost your fundraising by over 30% with our improved recurring add-on and start collecting donations effortlessly on your website.',
                        'give'
                    )}
                </p>

                <a href={' https://docs.givewp.com/recurring-dash'}>
                    <img src={`${assets}assets/dist/images/admin/white-external-icon.svg`} alt={'external link'} />
                    {__('Get more donations', 'give')}
                </a>
            </div>
        </div>
    );
}

export default Widget;
