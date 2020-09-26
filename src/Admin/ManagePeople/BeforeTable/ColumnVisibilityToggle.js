import React, { useEffect, useRef, useState, forwardRef } from "react"
import styled from "styled-components"
import store from "store"

const StyledDiv = styled.div`
  .dropdown-menu {
    position: absolute;
    background-color: white;
    min-width: 160px;
    box-shadow: 2px 2px 10px 0px rgba(0, 0, 0, 0.18);
    padding: 14px 18px;
    z-index: 1;
    border: 1px solid ${props => props.theme.borderColor};
  }
`

function useDropdown() {
  const [dropdownOpen, setDropdownOpen] = useState(false)

  // Close dropdown when clicking anywhere not within the dropdown
  const dropdownNode = useRef(null)
  const handleClickOutside = (e) => {
    if (dropdownNode.current.contains(e.target)){ return } // Inside click
    setDropdownOpen(false) // Outside click
  }
  useEffect(() => {
    document.addEventListener("mousedown", handleClickOutside)
    return () => {
      document.removeEventListener("mousedown", handleClickOutside)
    }
  }, [])

  return [ dropdownOpen, setDropdownOpen, dropdownNode ]

}

const ColumnVisibilityToggle = forwardRef(({ allColumns }, { headerGridRef, bodyGridRef }) => {
  const  [ dropdownOpen, setDropdownOpen, dropdownNode ] = useDropdown()

  // On toggling columns, store hidden column preferences and re-render react-window grids
  const handleColumnToggleClick = (e) => {
    const columnId = e.target.attributes['data-column-id'].value
    let hiddenColumns = store.get('hidden_columns')

    const index = hiddenColumns.indexOf(columnId)
    if (index > -1) {
      hiddenColumns.splice(index, 1)
    } else {
      hiddenColumns.push(columnId)
    }
    store.set('hidden_columns', hiddenColumns)

    headerGridRef.current.resetAfterColumnIndex(0, true)
    bodyGridRef.current.resetAfterColumnIndex(0, true)

  }

  return (
    <StyledDiv ref={dropdownNode}>
      <button className="button action" onClick={ e => setDropdownOpen(!dropdownOpen) }>
        Toggle Column Visibility
      </button>
      { dropdownOpen
        ?
        <div className="dropdown-menu">
          {allColumns.filter(c => !c?.disableVisibilityToggle).map(column => (
            <div key={column.id}>
              <label>
                <input
                  onClick={handleColumnToggleClick}
                  type="checkbox" {...column.getToggleHiddenProps()}
                  data-column-id={column.id}
                />{' '}
                {column.Header}
              </label>
            </div>
          ))}
        </div>
        : ''
      }
    </StyledDiv>
  )
})

export default ColumnVisibilityToggle