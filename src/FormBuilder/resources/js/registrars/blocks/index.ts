import {BlockConfiguration} from '@wordpress/blocks';

/**
 * @unreleased
 */
export interface Block {
    name: string;
    settings: BlockConfiguration;
}

/**
 * @unreleased
 */
interface Registrar {
    register(name: string, settings: BlockConfiguration): void;

    getAll(): Block[];

    get(blockName: string): Block | undefined;
}

/**
 * @unreleased
 */
export default class BlockRegistrar implements Registrar {
    /**
     * @unreleased
     */
    private blocks: Block[] = [];

    /**
     * @unreleased
     */
    public get(blockName: string): Block | undefined {
        return this.blocks.find(({name}) => name === blockName);
    }

    /**
     * @unreleased
     */
    public getAll(): Block[] {
        return this.blocks;
    }

    /**
     * @unreleased
     */
    public register(name, settings: BlockConfiguration): void {
        if (this.get(name)) {
            throw new Error(`Block "${name}" is already registered.`);
        }

        this.blocks.push({name, settings});
    }
}
