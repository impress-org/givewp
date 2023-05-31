import {ElementBlock} from '@givewp/form-builder/types/block';

import paragraph from './paragraph';

const ElementBlocks: ElementBlock[] = [paragraph];

const blockNames: string[] = ElementBlocks.map((block) => block.name);

export {blockNames};
export default ElementBlocks;
