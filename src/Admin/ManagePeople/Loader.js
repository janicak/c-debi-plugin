import React from "react"
import styled, { keyframes } from "styled-components"
import styledMap from "styled-map"

const rotate = keyframes`
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
`

const Loader = styled.div`
display: inline-block;
&:after {
  content: " ";
  display: block;
  position: relative;
  width: ${styledMap`
    small: 8px;
    default: 13px;
  `};
  height: ${styledMap`
    small: 8px;
    default: 13px;
  `};
  top: ${styledMap`
    small: 2px;
    default: 6px;
  `};
  margin: 0;
  border-radius: 50%;
  opacity: 0.5;
  border-style: solid;
  border-color: ${props => props.theme.textColor} transparent ${props => props.theme.textColor} transparent;
  border-width: ${styledMap`
    small: 1.8px;
    default: 3px;
  `};
  animation: ${rotate} 1.2s linear infinite;
}
`

export default Loader