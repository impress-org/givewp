import {__} from '@wordpress/i18n';
import {JSONSchemaType} from 'ajv';
import {CampaignInputFields} from './types';

const campaignSchema: JSONSchemaType<CampaignInputFields> = {
    type: "object",
    properties: {
        title: {
            type: 'string',
            minLength: 3,
            maxLength: 256,
            errorMessage: __('Enter Campaign title', 'give'),
            nullable: true
        },
        status: {
            type: 'string',
            enum: ['active', 'inactive', 'draft', 'pending', 'processing', 'failed'],
            nullable: true
        },
        longDescription: {
            type: 'string',
            default: '',
            nullable: true
        },
        goal: {
            type: 'integer',
            default: 10000,
            minimum: 0,
            nullable: true,
            errorMessage: __('Required field', 'give'),
        },
        goalType: {
            type: 'string',
            enum: ['amount', 'donations'],
            errorMessage: __('Required field', 'give'),
            nullable: true
        },
    },
    // @ts-ignore
    required: ['title'],
    additionalProperties: true
};

export default campaignSchema;
