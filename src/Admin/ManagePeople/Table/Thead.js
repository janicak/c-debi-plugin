import React, { useCallback, forwardRef, useEffect } from "react"
import styled from "styled-components"
import { VariableSizeGrid as Grid } from "react-window"

import Th from "./Th"

const StyledGrid = styled(Grid)`
  border-bottom: 1px solid ${props => props.theme.borderColor};
  height: ${props => props.theme.tableHeaderHeight};
  background-color: white;
`

const Thead = forwardRef(({ columns, minGridWidth, viewWidth, viewHeight, columnWidths }, { headerGridRef }) => {
  const RenderCell = useCallback(Th({ columns }), [columns])

  useEffect(() => {
    if (headerGridRef.current?.resetAfterColumnIndex) { headerGridRef.current.resetAfterColumnIndex(0, true) }
  }, [viewWidth])

  return(
    <StyledGrid
      columnCount={columns.length}
      overscanColumnCount={columns.length}
      columnWidth={(i) => columnWidths[i]}
      height={viewHeight}
      rowCount={1}
      rowHeight={() => viewHeight }
      width={viewWidth}
      ref={headerGridRef}
      style={{ overflow: "hidden" }}
    >
      { RenderCell }
    </StyledGrid>
  )
})

export default Thead