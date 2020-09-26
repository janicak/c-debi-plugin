import React, { forwardRef } from "react"
import styled from "styled-components"

import GlobalTextFilter from "./GlobalTextFilter"
import GroupFilters from "./GroupFilters"
import ColumnVisibilityToggle from "./ColumnVisibilityToggle"
import SelectedActions from "./SelectedActions"
import TestData from "./TestData"

const StyledDiv = styled.div`
  display: flex;
  justify-content: space-between;
  margin-bottom: 9px;
  align-items: flex-end;
  
  > div {
  
    > div {
      display: flex;
      align-items: center;
      
      &:first-child {
        margin-bottom: 7px;
      }
      
      &.left-bottom-area > div:last-child {
        border-left: 1px solid ${props => props.theme.darkBorderColor};
        margin-left: 5px;
        padding-left: 10px;
      }
    }
    
    label {
      font-weight: 600;
      padding-right: 5px;
      vertical-align: inherit;
    }
    button {
      margin-right: 5px !important;
    }
  }
`
const BeforeTable = forwardRef( ({
                         state, columns, allColumns, rows,
                         preGlobalFilteredRows, setGlobalFilter, setAllFilters,
                         toggleSortBy, selectedFlatRows, toggleAllRowsSelected
                       }, ref) => (

  <StyledDiv>
    <div className="left-area">
      <div className="left-top-area">
        <GlobalTextFilter
          preGlobalFilteredRows={preGlobalFilteredRows}
          globalFilter={state.globalFilter}
          setGlobalFilter={setGlobalFilter}
        />
        <GroupFilters
          setAllFilters={setAllFilters}
          filters={state.filters}
          columns={columns}
          toggleSortBy={toggleSortBy}
        />
      </div>
      <div className="left-bottom-area">
        <SelectedActions
          selectedFlatRows={selectedFlatRows}
          toggleAllRowsSelected={toggleAllRowsSelected}
        />
        <TestData />
      </div>
    </div>
    <div className="right-area">
      <div className="right-top-area" />
      <div className="right-bottom-area">
        <ColumnVisibilityToggle allColumns={allColumns} ref={ref}/>
        <div className="item-count">{rows.length} items</div>
      </div>
    </div>
  </StyledDiv>

))

export default BeforeTable