import matchSorter from "match-sorter"
import store from "store"

const fuzzyTextFilterFn = (rows, id, filterValue) => {
  const modValue = filterValue.replace(/[^\w\s]/g,'')
  return matchSorter(rows, modValue, { keys:
      [
        row => row.values['person_first_name_normalized'],
        row => row.values['person_last_name_normalized'],
        row => row.values['person_middle_name_normalized'],
        row => row.values['person_nickname_normalized'],
        row => row.values['person_current_placement'],
        row => row.values['person_degree'],
      ]
  })
}
fuzzyTextFilterFn.autoRemove = val => !val
export { fuzzyTextFilterFn }

export const userHiddenColumns = (defaultHiddenColumns) => {
  let columns = store.get("hidden_columns")
  if ( !columns ) {
    columns = defaultHiddenColumns
    store.set("hidden_columns", columns)
  }
  return columns
}

export const tableData = (data) => {

  let lastNames = {}

  const normalizeName = (str) => (
    str
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[\u2010-\u2015]/g, "-")
    .toLowerCase()
  )

  data.forEach((person, i) => {

    const lastName = normalizeName(person.name[0].last)
    const firstName = normalizeName(person.name[0].first)
    const middle = normalizeName(person.name[0].middle)
    const nickname = normalizeName(person.name[0].nickname)

    data[i].name[0].last_normalized = lastName
    data[i].name[0].first_normalized = firstName
    data[i].name[0].middle_normalized = middle
    data[i].name[0].nickname_normalized = nickname

    if (!lastNames.hasOwnProperty(lastName)){
      lastNames[lastName] = [];
    }

    lastNames[lastName].push({ rowIndex: i, firstName: firstName })
  })

  Object.entries(lastNames).forEach( ([lastName, persons]) => {
    if (persons.length > 1){
      let firstNames = {}
      persons.forEach( person => {
        data[person.rowIndex].nonUniqueLastName = "true";
        const firstName = person.firstName
        if (!firstNames.hasOwnProperty(firstName)){
          firstNames[firstName] = [];
        }
        firstNames[firstName].push(person.rowIndex)
        Object.entries(firstNames).forEach( ([firstName, personRowIndices]) => {
          if (personRowIndices.length > 1) {
            personRowIndices.forEach(personRowIndex => {
              data[personRowIndex].nonUniqueLastAndFirstName = "true"
            })
          } else {
            data[personRowIndices[0]].nonUniqueLastAndFirstName = "false"
          }
        })
      })
    } else {
      data[persons[0].rowIndex].nonUniqueLastName = "false"
      data[persons[0].rowIndex].nonUniqueLastAndFirstName = "false"
    }
  })

  return data
}

export const getScrollBarWidth = () => {
  let outside = document.createElement("div")
  let inside = document.createElement("div")
  outside.style.width = inside.style.width = "100%"
  outside.style.overflow = "scroll"
  document.body.appendChild(outside).appendChild(inside)
  const scrollbar = outside.offsetWidth - inside.offsetWidth
  outside.parentNode.removeChild(outside)
  return scrollbar
}

export const calculateWidths = (columns, viewWidth) => {
  columns.forEach( (col, i) => {
    columns[i].minWidth = col.minWidth ? col.minWidth : col.width
    //columns[i].maxWidth = col.maxWidth > 2000 ? false : col.maxWidth
    columns[i].calcWidth = col.minWidth
    columns[i].flexGrow = !(col.minWidth === col.width && col.width === col.maxWidth)
  })

  let minGridWidth = columns.reduce((acc, col) => acc + col.minWidth, 0)

  let flexRemainingSpace = viewWidth - minGridWidth

  while (flexRemainingSpace > 0){

    let flexDenominator = columns.reduce((acc, col) => col.flexGrow ? acc + col.width : acc, 0);
    let flexExpandWidthTotal = 0

    for (let i = 0; i < columns.length; i++) {

      if (columns[i].flexGrow){
        let flexRatio = columns[i].width / flexDenominator
        let flexExpandWidthColumn = flexRatio * flexRemainingSpace
        let calcWidth = columns[i].calcWidth + flexExpandWidthColumn
        columns[i].calcWidth = calcWidth

        // If the calculated width exceeds the max width, use the max width,
        // take the column out of future calculations, and restart the loop
        if (columns[i].maxWidth && ( calcWidth > columns[i].maxWidth ) ) {
          columns[i].flexGrow = false
          columns[i].calcWidth = columns[i].maxWidth
          flexExpandWidthColumn = columns[i].maxWidth - columns[i].minWidth
        }

        flexExpandWidthTotal += flexExpandWidthColumn

      }
    }

    flexRemainingSpace -= flexExpandWidthTotal

  }

  const columnWidths = columns.map((col) => col.calcWidth)

  return {
    minGridWidth,
    columnWidths
  }

}


