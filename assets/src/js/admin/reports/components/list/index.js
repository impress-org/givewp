import { useEffect, createRef } from 'react'

const List = ({onApproachScrollEnd, children}) => {

    const list = createRef()
    const listStyle = {
        height: '60px',
        overflow: 'scroll'
    }

    useEffect(() => {

        function checkScroll (evt) {

            const remaining = evt.target.scrollHeight - evt.target.scrollTop
            const height = evt.target.offsetHeight

            if (remaining <= height) {
                onApproachScrollEnd()
            }

        }
        
        list.current.addEventListener('scroll', checkScroll)
    
        return function cleanup () {
            list.current.removeEventListener('scroll', checkScroll)
        }

    }, [])

    return (
        <div ref={list} style={listStyle}>
            {children}
        </div>
    )
}
export default List