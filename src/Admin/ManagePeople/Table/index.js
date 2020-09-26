import React, { useMemo, useContext, useCallback, useRef} from "react"
import { useTable, useBlockLayout, useSortBy, useGlobalFilter, useFilters, useRowSelect } from "react-table"
import styled, {ThemeContext} from "styled-components"
import AutoSizer from "react-virtualized-auto-sizer"

import BeforeTable from "../BeforeTable"
import Thead from "./Thead"
import Tbody from "./Tbody"
import AdminCell from './AdminCell'
import DateCell from "./DateCell"
import SelectionCell from "./SelectionCell"
import enrichTableData from "./data"
import { fuzzyTextFilterFn, userHiddenColumns, calculateWidths } from "./helpers"

const columnSettings = () => [
  {
    id: 'selection',
    Cell: ({ row, state: { selectedRowIds } }) => (
      <div>
        <SelectionCell {...row.getToggleRowSelectedProps()} selectedRowIds={selectedRowIds} />
      </div>
    ),
    disableGlobalFilter: true,
    disableSortBy: true,
    disableVisibilityToggle: true,
    minWidth: 30,
    width: 30,
    maxWidth: 30
  },
  {
    Header: "ID",
    accessor: "ID",
    minWidth: 50,
    width: 50,
    maxWidth: 75
  },
  {
    Header: "Person",
    accessor: "post_title",
    disableGlobalFilter: true,
    Cell: AdminCell,
    minWidth: 200,
    width: 300,
  },
  {
    Header: "First",
    accessor: "person_first_name_normalized",
    Cell: ({ row: { original } }) => original.person_first_name,
    minWidth: 100,
    width: 100,
    maxWidth: 100
  },
  {
    Header: "Middle",
    accessor: "person_middle_name_normalized",
    Cell: ({ row: { original } }) => original.person_middle_name,
    minWidth: 100,
    width: 100,
    maxWidth: 100
  },
  {
    Header: "Last",
    accessor: "person_last_name_normalized",
    Cell: ({ row: { original } }) => original.person_last_name,
    minWidth: 125,
    width: 125,
    maxWidth: 125
  },
  {
    Header: "Nickname",
    accessor: "person_nickname_normalized",
    Cell: ({ row: { original } }) => original.person_nickname,
    minWidth: 100,
    width: 100,
    maxWidth: 100
  },
  {
    Header: "Current Placement",
    accessor: "person_current_placement",
    minWidth: 250,
    width: 250,
  },
  {
    Header: "Degree",
    accessor: "person_degree",
    minWidth: 250,
    width: 250,
  },
  {
    Header: "Non-unique Last",
    accessor: "nonUniqueLastName",
    minWidth: 150,
    width: 150,
    maxWidth: 150
  },
  {
    Header: "Non-unique Last & First",
    accessor: "nonUniqueLastAndFirstName",
    minWidth: 150,
    width: 150,
    maxWidth: 150
  },
  {
    Header: "Date Created",
    accessor: "post_date",
    Cell: DateCell,
    disableGlobalFilter: true,
    minWidth: 190,
    width: 190,
    maxWidth: 190
  },
  {
    Header: "Date Modified",
    accessor: "post_modified",
    Cell: DateCell,
    disableGlobalFilter: true,
    minWidth: 190,
    width: 190,
    maxWidth: 190
  },
  {
    Header: "Unattached",
    accessor: 'unattached',
    Cell: ({ cell }) => cell.value ? "true" : "false",
    disableGlobalFilter: true,
    minWidth: 100,
    width: 100,
    maxWidth: 100
  },
]

const StyledDiv = styled.div`
    background: white;
    border: 1px solid ${props => props.theme.borderColor};
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    background: #e6e6e6;
    
    > div {
      display: flex;
      flex-direction: column;
      
      > div {
        flex-shrink: 0;
      }
    }   
`

const Table = ({ data }) => {
  const {
    state,
    columns,
    allColumns,
    rows,
    prepareRow,
    getTableProps,
    preGlobalFilteredRows,
    setGlobalFilter,
    setAllFilters,
    toggleSortBy,
    selectedFlatRows,
    toggleAllRowsSelected
  } = useTable(
    {
      columns: useMemo(columnSettings, []),
      data: useMemo(() => enrichTableData(data), [data.length]),
      initialState: {
        hiddenColumns: useCallback(userHiddenColumns([ "nonUniqueLastName", "nonUniqueLastAndFirstName", "unattached" ]),[])
      },
      defaultColumn: useMemo(() => ({ width: 150 }), []),
      filterTypes: useMemo(() => ({
        text: fuzzyTextFilterFn
      }), []),
      globalFilter: 'text',
      autoResetSortBy: false,
      autoResetFilters: false,
    },
    useFilters,
    useGlobalFilter,
    useSortBy,
    useRowSelect,
    useBlockLayout
  )

  const visibleColumns = columns.filter(c => c.isVisible)

  const { tableHeight, tableHeaderHeight } = useContext(ThemeContext)

  const gridRefs = {
    headerGridRef: useRef(),
    bodyGridRef: useRef()
  }

  return (
    <>
      <BeforeTable
        state={state}
        columns={columns}
        allColumns={allColumns}
        rows={rows}
        preGlobalFilteredRows={preGlobalFilteredRows}
        setGlobalFilter={setGlobalFilter}
        setAllFilters={setAllFilters}
        toggleSortBy={toggleSortBy}
        selectedFlatRows={selectedFlatRows}
        toggleAllRowsSelected={toggleAllRowsSelected}
        ref={gridRefs}
      />
      <StyledDiv {...getTableProps()}>
        <AutoSizer style={{height: tableHeight, width: "100%"}}>
          { ({ width, height }) => {
            const {minGridWidth, columnWidths} = calculateWidths(visibleColumns, width)
            return (
              <>
                <Thead
                  ref={gridRefs}
                  columns={visibleColumns}
                  viewWidth={width - 2}
                  viewHeight={tableHeaderHeight}
                  minGridWidth={minGridWidth}
                  columnWidths={columnWidths}
                />
                <Tbody
                  ref={gridRefs}
                  columns={visibleColumns}
                  rows={rows}
                  viewWidth={width - 2}
                  viewHeight={height - tableHeaderHeight - 3}
                  prepareRow={prepareRow}
                  minGridWidth={minGridWidth}
                  columnWidths={columnWidths}
                />
              </>
            )
          }}
        </AutoSizer>
      </StyledDiv>
    </>
  )
}


export default Table