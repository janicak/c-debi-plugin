import moment from "moment"

const DateCell = ({ cell }) => (
  moment(cell.value).format('LLL')
)

export default DateCell