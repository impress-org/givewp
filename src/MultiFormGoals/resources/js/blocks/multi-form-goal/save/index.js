const {InnerBlocks} = wp.blockEditor;

const save = () => {
    return (
        <div className="give-multi-form-goal-block">
            <InnerBlocks.Content />
        </div>
    );
};
export default save;
