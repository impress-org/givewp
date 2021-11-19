import {Fragment} from 'react';

import DonationTable from '../../components/donation-table';
import Heading from '../../components/heading';
import Stats from './stats';

import {useSelector} from './hooks';

import {__} from '@wordpress/i18n';

const DashboardContent = () => {
    const donations = useSelector((state) => state.donations);
    const querying = useSelector((state) => state.querying);
    const error = useSelector((state) => state.error);

    if (querying) {
        return (
            <Fragment>
                <Heading icon="spinner">{__('Loading...', 'give')}</Heading>
            </Fragment>
        );
    }

    if (error) {
        return (
            <Fragment>
                <Heading icon="exclamation-triangle">{__('Error', 'give')}</Heading>
                <p style={{color: '#6b6b6b'}}>{error}</p>
            </Fragment>
        );
    }

    return (
        <Fragment>
            <Heading icon="chart-line">{__('Your Giving Stats', 'give')}</Heading>
            <Stats />
            <Heading icon="calendar-alt">
                {querying ? __('Loading...', 'give') : __('Recent Donations', 'give')}
            </Heading>
            {!querying && <DonationTable donations={donations} perPage={3} />}
        </Fragment>
    );
};

export default DashboardContent;
