import React from 'react'
import styled from 'styled-components'

const StyledDiv = styled.div`
  margin-top: 16px;
  background-color: white;
  padding: 1rem 1rem;
  border: 1px solid #ccd0d4;
  box-shadow: 0 1px 1px rgba(0,0,0,.04);
  h2 {
    margin-top: 0;
  }
`
const FormSection = ({heading, children}) => (
  <StyledDiv>
    <h2>{heading}</h2>
    { children }
  </StyledDiv>
)

export default FormSection