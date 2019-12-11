import Tab from '../tab'

const Tabs = (props) => {
    const links = Object.values(props.pages).map((page, index) => {
        if (page.show_in_menu === true) {
            return (
                <Tab to={page.path} key={index}>{page.title}</Tab>
            )
        }
    })
    return (
        <div className='nav-tab-wrapper give-nav-tab-wrapper'>
            {links}
        </div>
    )
}
export default Tabs