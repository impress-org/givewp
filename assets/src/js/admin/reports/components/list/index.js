import { useEffect } from 'react'

const List = ({onApproachScrolleEnd, children}) => {

    const list = createRef()

    useEffect(() => {

        function checkScroll (evt) {
            console.log('check scroll!', evt)
        }
        
        list.current.addEventListener('scroll', checkScroll)
    
        return function cleanup () {
            list.current.removeEventListener('scroll', checkScroll)
        }

    }, [])

    return (
        <div ref={list}>
            {children}
        </div>
    )
}
export default List