import AcfField from './AcfField'
import LookupSuggestion from "./LookupSuggestion"

const $ = window.jQuery

class AcfRadioSelectField extends AcfField {
  value
  LookupSuggestion

  constructor(fieldName) {
    super(fieldName)
    this.value = this.getValue()
  }

  getValue(){
    return this.Container
    .find("li label.selected span")
    .text()
  }

  setValue(value) {
    this.Container.find("li").each((i, li) => {
      const optionValue = $(li).find("label span").text()
      $(li).find("label input").prop("checked", value === optionValue)
    })
  }

  initLookupSuggestion(value, preview, note){
    this.LookupSuggestion = LookupSuggestion(preview, note, (e) => {
      e.preventDefault()
      this.setValue(value)
    })
    this.Container.append(this.LookupSuggestion)
  }

}

export default AcfRadioSelectField