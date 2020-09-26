import RepeaterFieldHeaderCell from "./HeaderCell"
import RepeaterFieldHeaderCellEntityField from "./HeaderCellEntityField"

const $ = window.jQuery

class RepeaterFieldHeaderRow {
  Container
  fieldName
  cells = {}

  constructor(Container, fieldName, cellsConfig){
    this.Container = Container
    this.fieldName = fieldName
    this.initCells(cellsConfig)
  }

  getCells(){
    return this.Container.find('th.acf-th')
  }

  initCells(cellsConfig){
    this.getCells().each((i, cellContainer) => {
      this.initCell($(cellContainer), cellsConfig)
    })
  }

  initCell(cellContainer, cellsConfig){
    let Cell

    const cellName = cellContainer.data("name")
    const cellType = cellContainer.data("type")
    const cellConfig = cellsConfig.hasOwnProperty(cellName) ? cellsConfig[cellName] : null

    switch (cellType) {
      case "post_object":
        Cell = new RepeaterFieldHeaderCellEntityField(cellContainer, this.fieldName, cellType, cellName, cellConfig)
        break;
      default:
        Cell = new RepeaterFieldHeaderCell(cellContainer, this.fieldName, cellType, cellName, cellConfig)
    }

    this.cells[cellName] = Cell
  }
}

export default RepeaterFieldHeaderRow