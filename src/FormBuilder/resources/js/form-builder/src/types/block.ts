import {BlockConfiguration} from '@wordpress/blocks';

export interface FieldBlock {
    name: string;
    settings: BlockConfiguration;
}

export interface ElementBlock extends FieldBlock {}

export interface SectionBlock extends FieldBlock {}
