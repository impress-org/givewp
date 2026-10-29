import {getItemName} from './getItemName';

describe('getItemName', () => {
    it('uses the donor, then the title, then the donor information, then the id', () => {
        expect(getItemName({id: 1, donor: 'Jane', title: 'x'})).toBe('Jane');
        expect(getItemName({id: 2, title: '<strong>Spring</strong>'})).toBe('<strong>Spring</strong>');
        expect(getItemName({id: 3, donorInformation: 'Joe'})).toBe('Joe');
        expect(getItemName({id: 4})).toBe('4');
        expect(getItemName({id: 5, title: ''})).toBe('5');
    });
});
