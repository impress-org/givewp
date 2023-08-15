import {BlockConfiguration} from '@wordpress/blocks';

/**
 * @since 3.0.0
 */
export interface Block {
    name: string;
    settings: BlockConfiguration;
}

/**
 * @since 3.0.0
 */
interface Registrar {
    register(name: string, settings: BlockConfiguration): void;

    getAll(): Block[];

    get(blockName: string): Block | undefined;
}

/**
 * @since 3.0.0
 */
export default class BlockRegistrar implements Registrar {
    /**
     * @since 3.0.0
     */
    private blocks: Block[] = [];

    /**
     * @since 3.0.0
     */
    public get(blockName: string): Block | undefined {
        return this.blocks.find(({name}) => name === blockName);
    }

    /**
     * @since 3.0.0
     */
    public getAll(): Block[] {
        return this.blocks;
    }

    /**
     * @since 3.0.0
     */
    public register(name, settings: BlockConfiguration): void {
        if (this.get(name)) {
            throw new Error(`Block "${name}" is already registered.`);
        }

        this.blocks.push({name, settings});
    }
}
