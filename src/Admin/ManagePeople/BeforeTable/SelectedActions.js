import React, {useContext, useCallback, useState} from "react"
import styled from "styled-components"
import { useToasts } from "react-toast-notifications"

import PersonDetails from "../Modal/PersonDetails"
import Loader from "../Loader"
import { AppContext } from "../App"
import { deletePeople } from "../requests"

const StyledDiv = styled.div`
`

const SelectedActions = ({selectedFlatRows, toggleAllRowsSelected}) => {
  const rows = selectedFlatRows.map(r => r.original)

  const { data, setData, openModal } = useContext(AppContext)
  const modalContent = useCallback(() => <PersonDetails rows={rows} />, [rows])

  const [deleting, setDeleting] = useState(false)
  const { addToast } = useToasts()
  const handleDeleteClick = async () => {
    setDeleting(true)
    const { deleted } = await deletePeople(rows.map(r => r.ID))
    setDeleting(false)
    const newData = data.filter(r => deleted.indexOf(`${r.ID}`) === -1 )
    setData(newData)
    addToast(`Deleted Selected People`, { appearance: "success" })
  }

  return(
    <StyledDiv>
      <button className={`button action${!selectedFlatRows.length ? " disabled" : ""}`} onClick={()=> toggleAllRowsSelected(false)}>
        Clear Selected
      </button>
      <button className={`button action${selectedFlatRows.length !== 2 ? " disabled" : ""}`} onClick={() => openModal(modalContent)}>
        Compare & Merge Selected
      </button>
      <button className={`button action${!selectedFlatRows.length ? " disabled" : ""}`} onClick={() => handleDeleteClick()}>
        Delete Selected
      </button>
      { deleting && <Loader/> }
    </StyledDiv>
  )
}

export default SelectedActions


