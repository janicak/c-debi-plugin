class RepeaterFieldHeaderCell {
  Container
  fieldName
  cellType
  cellName
  cellConfig

  constructor(Container, fieldName, cellType, cellName, cellConfig){
    this.Container = Container
    this.fieldName = fieldName
    this.cellType = cellType
    this.cellName = cellName
    this.cellConfig = cellConfig
  }
}

export default RepeaterFieldHeaderCell