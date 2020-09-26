import React from "react";
import styled from "styled-components";

const StyledDiv = styled.div`
  a { cursor: pointer; }
  .separator {
    color: ${props => props.theme.darkBorderColor};
  }
`
const GroupFilters = ({ setAllFilters, filters, toggleSortBy, columns }) => {

  const AddFilterLink = ({ filters, sortBys, children }) => (
    <a onClick={e => {
      setAllFilters(filters)
      columns.forEach(col => { if (col.isSorted) { col.clearSortBy() }})
      sortBys.forEach(sortBy => toggleSortBy(...sortBy))
    }}>{ children }</a>
  )

  const ClearFiltersLink = ({ children }) => (
    <a style={{fontStyle: 'italic' }} onClick={e => {
      setAllFilters([])
      columns.forEach(col => { if (col.isSorted) { col.clearSortBy() }})
    }}><span style={{ textDecoration: 'underline'}}>{ children }</span> ×</a>
  )

  const activeFilters= filters.reduce((acc, f) => {
    if (f?.id) { acc[f.id] = f.value }
    return acc
  }, {})

  return(
    <StyledDiv><label>Filter Groups:</label>
      { !activeFilters?.nonUniqueLastName
        ? <AddFilterLink
            filters={[{ id: 'nonUniqueLastName', value: "true" }]}
            sortBys={[['person_last_name_normalized']]}
          >
            Non-unique last names
          </AddFilterLink>
        : <ClearFiltersLink>Non-unique last names</ClearFiltersLink>
      } <span className="separator">|</span> {' '}
      { !activeFilters?.nonUniqueLastAndFirstName
        ? <AddFilterLink
            filters={[{ id: 'nonUniqueLastAndFirstName', value: "true" }]}
            sortBys={[['person_first_name_normalized', false, true], ['person_last_name_normalized', false, true]]}
          >
            Non-unique first & last names
          </AddFilterLink>
        : <ClearFiltersLink>Non-unique first & last names</ClearFiltersLink>
      } <span className="separator">|</span> {' '}
      { !activeFilters?.unattached
        ? <AddFilterLink
          filters={[{ id: 'unattached', value: "true" }]}
          sortBys={[['person_first_name_normalized', false, true], ['person_last_name_normalized', false, true]]}
        >
          Unattached People
        </AddFilterLink>
        : <ClearFiltersLink>Unattached People</ClearFiltersLink>
      }
    </StyledDiv>
  )
}

export default GroupFilters