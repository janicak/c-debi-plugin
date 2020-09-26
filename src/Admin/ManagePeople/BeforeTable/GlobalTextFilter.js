import React from "react";
import styled from "styled-components";

const StyledP = styled.p`
  margin: 0 5px 0 0;
`
const GlobalTextFilter = ({ globalFilter, setGlobalFilter }) => {
  return (
    <StyledP>
      <label htmlFor="table-text-filter">
        Text Filter:
      </label>
      <input
        id="table-text-filter"
        type="search"
        value={globalFilter || ''}
        onChange={e => {
          setGlobalFilter(e.target.value || undefined) // Set undefined to remove the filter entirely
        }}
      />
    </StyledP>
  )
}

export default GlobalTextFilter