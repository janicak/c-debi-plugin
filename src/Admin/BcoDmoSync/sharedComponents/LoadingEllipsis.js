import React from 'react'
import styled, { keyframes } from 'styled-components'


const ellipsis = keyframes`
  to {
    width: 1em;    
  }
`
const StyledLoadingEllipsis = styled('span')`
  &:after {
    overflow: hidden;
    display: inline-block;
    vertical-align: bottom;
    animation: ${ellipsis} steps(4,end) 2000ms infinite;
    content: "\\2026"; /* ascii code for the ellipsis character */
    width: 0px;
  }
`
const LoadingEllipsis = () => (
  <StyledLoadingEllipsis />
)

export default LoadingEllipsis