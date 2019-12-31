import Tab from '../tab'

const Tabs = (props) => {
    return (
        <div className='nav-tab-wrapper give-nav-tab-wrapper'>
            <Tab to='/'>
                Overview
            </Tab>
            <Tab to='/edit.php?post_type=give_forms&page=give-reports'>
                Legacy Reports Page
            </Tab>
        </div>
    )
}
export default Tabs