const $ = window.jQuery

class AcfField {
  fieldName
  Container

  constructor(fieldName) {
    this.fieldName = fieldName
    this.initContainer()
  }

  initContainer(){
    this.Container = $(`.acf-field[data-name="${this.fieldName}"] > .acf-input`)
  }
}

export default AcfField