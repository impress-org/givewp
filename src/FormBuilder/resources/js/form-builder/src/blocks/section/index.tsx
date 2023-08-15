import {SectionBlock} from '@givewp/form-builder/types/block';

import settings from './settings';

const section: SectionBlock = {
    name: 'givewp/section',
    settings,
};

const sectionBlocks: SectionBlock[] = [section];

const sectionBlockNames: string[] = sectionBlocks.map(({name}) => name);

export default sectionBlocks;
export {sectionBlockNames};
