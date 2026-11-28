import { getSubscriptionOptionsWindowData } from "@givewp/subscriptions/utils";

const { legacyApiRoot, apiNonce } = getSubscriptionOptionsWindowData();

const apiSettings = {
    apiRoot: `${legacyApiRoot}/donations`,
    apiNonce,
    table: {
        columns: [
            {
                id: 'id',
                label: 'ID',
                sortable: true,
                visible: true,
            },
            {
                id: 'subscriptionDonationType',
                label: 'Type',
                sortable: false,
                visible: true,
            },
            {
                id: 'campaign',
                label: 'Campaign',
                sortable: false,
                visible: true,
            },
            {
                id: 'createdAt',
                label: 'Date',
                sortable: true,
                visible: true,
            },
            {
                id: 'status',
                label: 'Status',
                sortable: false,
                visible: true,
            },
            {
                id: 'amount',
                label: 'Amount',
                sortable: true,
                visible: true,
            },
        ],
        id: 'donations',
    },
};

export default apiSettings;
