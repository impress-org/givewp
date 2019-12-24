import moment from 'moment'

export const reducer = (state, action) => {
    switch (action.type) {
        case 'SET_DATES':
            return {
                ...state,
                period: {
                    startDate: action.payload.startDate,
                    endDate: action.payload.endDate,
                    range: 'custom'
                }
            }
        case 'SET_RANGE':
            //determine new startDate based on selected range
            let startDate
            switch (action.payload.range) {
                case 'day':
                    startDate = moment(state.period.endDate)
                    break
                case 'week':
                    startDate = moment(state.period.endDate).subtract(7, 'days')
                    break
                case 'month':
                    startDate = moment(state.period.endDate).subtract(1, 'months')
                    break
                case 'year':
                    startDate = moment(state.period.endDate).subtract(1, 'years')
                    break
            }
            return {
                ...state,
                period: { ...state.period, 
                    startDate,
                    range: action.payload.range
                }
            }
        default:
            return state;
    }
}