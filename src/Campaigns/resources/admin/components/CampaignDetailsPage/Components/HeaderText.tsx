/**
 * @unreleased
 */
const HeaderText = ({children}) => {
    return (
        <div style={{
            fontSize: '16px',
            fontWeight: 600,
            lineHeight: '24px',
        }}>
            {children}
        </div>
    )
}

export default HeaderText;
