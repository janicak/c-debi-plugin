import LookupSuggestion from "./LookupSuggestion"

const $ = window.jQuery

class PostTitle {
  Container
  LookupSuggestion
  Label
  Input
  value

  constructor() {
    this.initContainer()
    this.initInput()
    this.initLabel()
    this.value = this.getValue()
  }

  initContainer(){
    this.Container = $("#titlewrap")
  }

  initInput(){
    this.Input = this.Container.find('input')
  }

  initLabel(){
    this.Label = this.Container.find('label')
  }

  getValue() {
    return this.Input.val()
  }

  setValue(value) {
    this.Input.val(value)
    if (value) {
      this.Label.addClass("screen-reader-text")
    } else {
      this.Label.removeClass("screen-reader-text")
    }
  }

  initLookupSuggestion(value, preview, note){
    this.LookupSuggestion = LookupSuggestion(preview, note, (e) => {
      e.preventDefault()
      this.setValue(value)
    })
    this.Container.append(this.LookupSuggestion)
  }
}

export default PostTitle