import PropTypes from 'prop-types';
import { useEffect, createRef } from 'react'
import './style.scss';

const List = ({onScrollEnd, children}) => {
    
    const list = createRef()

    useEffect(() => {

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

    }, [onScrollEnd])

    return (
        <div ref={list} className='list'>
            {children}
        </div>
    )
}

List.propTypes = {
    onScrollEnd: PropTypes.func,
    children: PropTypes.node.isRequired
}

List.defaultProps = {
    onScrollEnd: null,
    children: null
}

export default List