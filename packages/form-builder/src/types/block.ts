export type Block = {
    clientId: string;
    name: string;
    isValid: boolean;
    attributes?: {
        title?: string;
        description?: string;
    }
    innerBlocks?: Block[]
}
