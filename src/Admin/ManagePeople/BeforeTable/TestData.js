import React, { useContext, useState } from "react"
import styled from "styled-components"
import { useToasts } from "react-toast-notifications"

import { AppContext } from "../App"
import Loader from "../Loader"
import { resetAndCreateTestData, removeTestData } from "../requests"

const StyledDiv = styled.div`
  button {
    margin-right: 5px !important;
  }
`

const TestData = () => {
  const [ loading, setLoading] = useState(false)
  const { addToast } = useToasts()
  const { data, setData } = useContext(AppContext)

  const handleCreateResetClick = async () => {
    setLoading(true)
    const { created, deleted } = await resetAndCreateTestData()
    setLoading(false)
    let newData = data.filter(r => deleted.indexOf(r.ID) === -1 )
    newData.unshift(...created)
    setData(newData)
    addToast("Reset and Created Test Data", { appearance: "success" })
  }

  const handleRemoveClick = async () => {
    setLoading(true)
    const { deleted } = await removeTestData()
    setLoading(false)
    const newData = data.filter(r => deleted.indexOf(r.ID) === -1 )
    setData(newData)
    addToast("Removed Test Data", { appearance: "success" })
  }

  return(
    <StyledDiv>
      <button className="button action" onClick={handleCreateResetClick}>
        Create / Reset Test Data
      </button>
      <button className="button action" onClick={handleRemoveClick}>
        Remove Test Data
      </button>
      { loading && <Loader />}
    </StyledDiv>
  )
}

export default TestData