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
export default List