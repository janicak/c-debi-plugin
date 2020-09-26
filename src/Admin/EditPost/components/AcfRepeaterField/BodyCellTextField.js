import RepeaterFieldBodyCell from "./BodyCell"

class RepeaterFieldBodyCellTextField extends RepeaterFieldBodyCell {
  value
  Input

  constructor(Container, fieldName, rowId, cellType, cellName, cellConfig){
    super(Container, fieldName, rowId, cellType, cellName, cellConfig)
    this.initInput()
    this.value = this.getValue()
  }

  initInput(){
    this.Input = this.Container.find("input")
  }

  getValue(){
    return this.Input.val()
  }

  setValue(value){
    this.Input.val(value)
  }

}

export default RepeaterFieldBodyCellTextField