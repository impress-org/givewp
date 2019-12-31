import Tab from '../tab'

const Tabs = (props) => {
    return (
        <div className='nav-tab-wrapper give-nav-tab-wrapper'>
            <Tab to='/'>
                Overview
            </Tab>
            <a className='nav-tab' href='/wp-admin/edit.php?post_type=give_forms&page=give-reports'>Legacy Reports Page</a>
        </div>
    )
}
export default Tabs