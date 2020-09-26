import RepeaterFieldBodyCellTextField from "./BodyCellTextField"
import RepeaterFieldBodyCellEntityField from "./BodyCellEntityField"

const $ = window.jQuery

class RepeaterFieldBodyRow {
  Container
  fieldName
  rowId
  cells = {}

  constructor(Container, fieldName, cellsConfig){
    this.Container = Container
    this.fieldName = fieldName
    this.rowId = Container.data("id")
    this.initCells(cellsConfig)
  }

  getCells(){
    return this.Container.children('td.acf-field')
  }

  initCells(cellsConfig){
    this.getCells().each((i, cellContainer) => {
      this.initCell($(cellContainer), cellsConfig)
    })
  }

  initCell(cellContainer, cellsConfig){
    const cellName = cellContainer.data("name")
    const cellType = cellContainer.data("type")
    const cellConfig = cellsConfig.hasOwnProperty(cellName) ? cellsConfig[cellName] : null

    let Cell
    switch (cellType) {
      case "post_object":
        Cell = new RepeaterFieldBodyCellEntityField(cellContainer, this.fieldName, this.rowId, cellType, cellName, cellConfig)
        break;
      default:
        Cell = new RepeaterFieldBodyCellTextField(cellContainer, this.fieldName, this.rowId, cellType, cellName, cellConfig)
    }
    this.cells[cellName] = Cell
  }

}

export default RepeaterFieldBodyRow