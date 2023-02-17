import paragraph from './paragraph';

const ElementBlocks = [paragraph];

const blockNames = ElementBlocks.map((block) => block.name);

export default ElementBlocks;
export {blockNames};
