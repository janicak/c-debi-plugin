import React, { useCallback, useContext, forwardRef, useEffect } from 'react'
import { VariableSizeGrid as Grid } from "react-window"
import styled, { ThemeContext } from "styled-components"

import Td from "./Td"

const StyledGrid = styled(Grid)`
`
const Tbody = forwardRef(({ columns, rows, prepareRow, minGridWidth, viewHeight, viewWidth, columnWidths }, { headerGridRef, bodyGridRef }) => {
  const { tableBodyCellHeight } = useContext(ThemeContext)
  const xScrollVisible = viewWidth < minGridWidth
  const RenderCell = useCallback(Td({ rows, prepareRow, columns, xScrollVisible }), [rows, prepareRow])

  useEffect(() => {
    if ( bodyGridRef.current?.resetAfterColumnIndex ) {
      bodyGridRef.current.resetAfterColumnIndex(0, true)
    }
  }, [viewWidth])

  return (
    <StyledGrid
      columnCount={columns.length}
      overscanColumnCount={columns.length}
      overscanRowCount={20}
      columnWidth={(i) => columnWidths[i]}
      width={viewWidth}
      height={viewHeight}
      rowCount={rows.length}
      rowHeight={() => tableBodyCellHeight }
      ref={bodyGridRef}
      onScroll={({ scrollLeft }) =>
        headerGridRef.current.scrollTo({ scrollLeft })
      }
      style={{ overflowX: `${ xScrollVisible ? "auto" : "hidden" }`, overflowY: "scroll"}}
    >
      { RenderCell }
    </StyledGrid>
  )
})

export default Tbody