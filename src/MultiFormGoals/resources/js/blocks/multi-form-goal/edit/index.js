/**
 * WordPress dependencies
 */
const {__} = wp.i18n;
const {InnerBlocks} = wp.blockEditor;
const {useEffect} = wp.element;
const {select, dispatch} = wp.data;

const edit = ({isSelected, clientId}) => {
    // When adding a new Multi-Form Goal block, select the inner Progress Bar block by default
    useEffect(() => {
        if (isSelected) {
            selectProgressBar();
        }
    }, []);

    const selectProgressBar = () => {
        const parentBlock = select('core/editor').getBlocksByClientId(clientId)[0];
        const progressBarBlock = parentBlock.innerBlocks[parentBlock.innerBlocks.length - 1];
        dispatch('core/block-editor').selectBlock(progressBarBlock.clientId);
    };

    const blockTemplate = [
        [
            'core/media-text',
            {
                imageFill: true,
            },
            [
                [
                    'core/heading',
                    {
                        placeholder: __('Heading', 'give'),
                    },
                ],
                [
                    'core/paragraph',
                    {
                        placeholder: __('Summary', 'give'),
                    },
                ],
            ],
        ],
        ['give/progress-bar', {}],
    ];

    return (
        <div className="give-multi-form-goal-block">
            <InnerBlocks template={blockTemplate} templateLock="all" />
        </div>
    );
};
export default edit;
