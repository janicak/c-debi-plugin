import React, { useContext, useCallback, useState } from "react"
import styled from "styled-components"
import { useToasts } from "react-toast-notifications"

import PersonDetails from "../Modal/PersonDetails"
import Loader from "../Loader"
import { deletePeople } from '../requests'
import { AppContext } from "../App"
import {entityToChar} from "../helpers"


const StyledDiv = styled.div`
  a { cursor: pointer; }
  ${Loader}{
    padding-left: 4px;
  }
`
const AdminCell = ({ cell, row }) => {
  const { openModal, data, setData } = useContext(AppContext)
  const modalContent = useCallback(() => <PersonDetails rows={[row.original]} />, [row])
  const [deleting, setDeleting] = useState(false)
  const { addToast } = useToasts()
  const handleDeleteClick = async (ID) => {
    setDeleting(true)
    const { deleted } = await deletePeople([ID])
    setDeleting(false)
    const newData = data.filter(r => deleted.indexOf(`${r.ID}`) === -1 )
    setData(newData)
    addToast(`Deleted Person ID: ${ID}`, { appearance: "success" })
  }
  return (
    <StyledDiv>
      <div>{cell.value}</div>
      <div>
        <a href="#" onClick={() => openModal(modalContent) }>View</a>
        {' '} <span className="separator">|</span> {' '}
        <a href={entityToChar(row.original.edit_link)} target="_blank">Edit</a>
        {' '} <span className="separator">|</span> {' '}
        <a href="#" onClick={() => handleDeleteClick(row.original.ID) }>Delete</a>
        { deleting && <Loader small/>}
      </div>
    </StyledDiv>
  )
}

export default AdminCell

