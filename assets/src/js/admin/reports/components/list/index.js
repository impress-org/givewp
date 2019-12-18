import { useEffect, createRef } from 'react'

const List = ({onApproachScrollEnd, children}) => {

    const list = createRef()
    const listStyle = {
        height: '60px',
        overflow: 'scroll'
    }

    const itemStyle = {
        height: '200px',
        background: '#CCCCCC'
    }

    useEffect(() => {

        function checkScroll (evt) {

            const remaining = evt.target.scrollHeight - evt.target.scrollTop
            const height = evt.target.offsetHeight

            if (remaining <= height) {
                console.log('remaining!', remaining)
            }

        }
        
        list.current.addEventListener('scroll', checkScroll)
    
        return function cleanup () {
            list.current.removeEventListener('scroll', checkScroll)
        }

    }, [])

    return (
        <div ref={list} style={listStyle}>
            <div style={itemStyle}></div>
            {children}
        </div>
    )
}
export default List