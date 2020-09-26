import React from 'react'
import styled from "styled-components"

const StyledDiv = styled.div`
  color: ${props => props.theme.textColor};
  height: ${props => `${40 - (props.theme.cellPaddingV * 2)}px`};
  padding-right: ${props => props.theme.scrollBarWidth}px;
 
  & > div {
    padding: ${props => `${props.theme.cellPaddingV}px ${props.theme.cellPaddingH}`}px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    height: ${props => `${40 - (props.theme.cellPaddingV * 2)}px`};
    &.last-th {
      padding-right: ${props => props.theme.scrollBarWidth + props.theme.cellPaddingH}px;
    }
  }
  
  .sorting-indicator {
    margin-top: 3px;
  }
 
  &.asc a:focus span.sorting-indicator, &.asc:hover span.sorting-indicator, 
  &.desc a:focus span.sorting-indicator, &.desc:hover span.sorting-indicator, 
  &.sorted .sorting-indicator, &.sortable:hover .sorting-indicator {
      visibility: visible;
  }
  &.sorted.asc .sorting-indicator:before,  {
    content: "\\f142";
  } 
  &.sorted.desc .sorting-indicator:before, &.sorted.asc:hover .sorting-indicator:before {
      content: "\\f140";
  }
  &.sorted.desc:hover .sorting-indicator:before {
      content: "\\00D7";
      font-size: 14px;
      left: -1px;
      top: -3.1px;
  }
`

const Th = ({ columns }) => ({ columnIndex, rowIndex, style }) => {
  const column = columns[columnIndex]

  return (
    <StyledDiv {...column.getSortByToggleProps({ style })}
         className={`${ column.disableSortBy ? "" : column.isSorted ? column.isSortedDesc ? " sorted desc" : " sorted asc" : " sortable"}`}
    >
      <div className={`${columnIndex === columns.length - 1 ? " last-th" : ""}`}>
        { column.disableSortBy
          ? <span>{column.render("Header")}</span>
          : <a>{column.render("Header")}</a>
        }
        <span className="sorting-indicator"/>
      </div>
    </StyledDiv>
  )
}

export default Th