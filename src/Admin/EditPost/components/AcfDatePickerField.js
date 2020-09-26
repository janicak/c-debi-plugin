import AcfField from "./AcfField"
import LookupSuggestion from "./LookupSuggestion"

class AcfDatePickerField extends AcfField {
  value
  preview
  ValueInput
  PreviewInput
  LookupSuggestion

  constructor(fieldName){
    super(fieldName)
    this.initValueInput()
    this.initPreviewInput()
    this.value = this.getValue()
    this.preview = this.getPreview()
  }

  initValueInput(){
    this.ValueInput = this.Container.find('input[type="hidden"]')
  }

  initPreviewInput(){
    this.PreviewInput = this.Container.find('input.hasDatepicker')
  }

  getValue() {
    return this.ValueInput.val()
  }

  getPreview() {
    return this.PreviewInput.val()
  }

  setValue(value, preview){
    this.ValueInput.val(value)
    this.PreviewInput.val(preview)
    this.value = value
    this.preview = preview
  }

  initLookupSuggestion(value, preview, note){
    this.LookupSuggestion = LookupSuggestion(preview, note, (e) => {
      e.preventDefault()
      this.setValue(value, preview)
    })
    this.Container.append(this.LookupSuggestion)
  }
}

export default AcfDatePickerField