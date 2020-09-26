import AcfField from './AcfField'
import LookupSuggestion from "./LookupSuggestion"

export class AcfTextField extends AcfField {
  Input
  LookupSuggestion
  value

  constructor(fieldName) {
    super(fieldName)
    this.initInput()
    this.value = this.getValue()
  }

  initInput(){
    this.Input = this.Container.find("input")
  }

  getValue() {
    return this.Input.val()
  }

  setValue(value) {
    this.Input.val(value)
    this.value = value
  }

  initLookupSuggestion(value, preview, note){
    this.LookupSuggestion = LookupSuggestion(preview, note, (e) => {
      e.preventDefault()
      this.setValue(value)
    })
    this.Container.append(this.LookupSuggestion)
  }
}

export default AcfTextField