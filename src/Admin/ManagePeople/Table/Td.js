import React from 'react'
import styled from "styled-components"

const StyledDiv = styled.div`
  background-color: ${props => props.parity === 'even' ? "white" : "#f9f9f9"};
  color: ${props => props.theme.textColor};
  & > div {
    padding: ${props => `${props.theme.cellPaddingV}px ${props.theme.cellPaddingH}px`};
    &.last-td {
      padding-right: ${props => !props.xScrollVisible ? (props.theme.cellPaddingH + props.theme.scrollBarWidth) : props.theme.cellPaddingH}px;
    }
  }
`

const Td = ({ rows, prepareRow, columns }) => ({ columnIndex, rowIndex, style }) => {
  const row = rows[rowIndex]
  prepareRow(row)
  return (
    <StyledDiv
      style={style}
      parity={rowIndex % 2 ? "even" : "odd"}
    >
      <div className={`${columnIndex === columns.length - 1 ? " last-td" : ""}`}>
        {row.cells[columnIndex].render('Cell')}
      </div>
    </StyledDiv>
  )
}

export default Td