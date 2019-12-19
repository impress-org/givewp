import PropTypes from 'prop-types';
import { useEffect, createRef } from 'react'
import './style.scss';

const List = ({onScrollEnd, children}) => {
    
    const list = createRef()

    useEffect((onScrollEnd) => {

        function checkScroll (evt) {

            const remaining = evt.target.scrollHeight - evt.target.scrollTop
            const height = evt.target.offsetHeight

            if (remaining <= height) {
                onScrollEnd()
            }

        }
        
        if (onScrollEnd) {
            list.current.addEventListener('scroll', checkScroll)
            return function cleanup () {
                list.current.removeEventListener('scroll', checkScroll)
            }
        }

    }, [])

    return (
        <div ref={list} className='list'>
            {children}
        </div>
    )
}

List.propTypes = {
    onScrollEnd: PropTypes.func,
    children: PropTypes.node
}

List.defaultProps = {
    onScrollEnd: null,
    children: [
        <div className='default-item'>
            Error loading list.
        </div>
    ]
}

export default List