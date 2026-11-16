import listTableScreen from './listTableScreen';
import apiSettings from './apiSettings';

jest.mock('@givewp/subscriptions/utils', () => ({
    getSubscriptionOptionsWindowData: () => ({legacyApiRoot: '/api', apiNonce: 'nonce'}),
}));

describe('subscription Donations tab screen', () => {
    it('lays the cards out with fields that exist in the tab columns', () => {
        const ids = apiSettings.table.columns.map((column) => column.id);
        const roles = listTableScreen.fieldRoles!;

        expect(ids).toEqual(expect.arrayContaining([roles.primary, roles.description, ...roles.secondary]));
    });
});
