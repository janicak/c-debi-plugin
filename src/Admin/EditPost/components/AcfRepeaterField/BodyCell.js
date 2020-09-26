class RepeaterFieldBodyCell {
  Container
  fieldName
  rowId
  cellType
  cellName
  cellConfig

  constructor(Container, fieldName, rowId, cellType, cellName, cellConfig){
    this.Container = Container
    this.fieldName = fieldName
    this.rowId = rowId
    this.cellType = cellType
    this.cellName = cellName
    this.cellConfig = cellConfig
  }

}

export default RepeaterFieldBodyCell