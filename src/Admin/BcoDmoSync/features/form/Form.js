import React from 'react'
import styled from 'styled-components'
import Sync from './Sync'
import LinkPeople from "./LinkPeople"
import FormExplanation from "./FormExplanation.mdx"

const StyledForm = styled.div`
  max-width: 800px;
`
const Form = () => {
  return(
    <StyledForm >
      <FormExplanation />
      <Sync />
      <LinkPeople />
    </StyledForm >
  )
}

export default Form