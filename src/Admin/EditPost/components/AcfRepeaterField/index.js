import AcfField from '../AcfField'
import RepeaterFieldBodyRow from "./BodyRow"
import RepeaterFieldHeaderRow from "./HeaderRow"
import LookupSuggestion from "../LookupSuggestion"
import { getState } from "../../state"

const $ = window.jQuery

class AcfRepeaterField extends AcfField {
  cellsConfig
  rows = {}
  headerRow
  LookupSuggestion

  constructor(fieldName, cellsConfig) {
    super(fieldName)
    this.cellsConfig = cellsConfig
    this.initRows()
    this.initRowChangeHandlers()
    this.initHeader()
  }

  getRowContainers() {
    return this.Container.find('tr.acf-row:not(.acf-clone)')
  }

  initRows() {
    this.getRowContainers().each((i, rowContainer) => {
      this.initRow($(rowContainer))
    })
  }

  initRow(rowContainer) {
    const rowId = rowContainer.data("id")
    if ( !this.rows.hasOwnProperty(rowId) ) {
      this.rows[rowId] = new RepeaterFieldBodyRow(rowContainer, this.fieldName, this.cellsConfig)
    }
  }

  addRow() {
    this.Container.find('.acf-actions .acf-button[data-event="add-row"]').trigger("click")
    return this.getRowContainers().last().data("id")
  }

  deleteRows() {
    this.getRowContainers().each((i, rowContainer) => this.deleteRow($(rowContainer)))
  }

  deleteRow(rowContainer){
    const rowId = rowContainer.data("id")

    if (this.rows.hasOwnProperty(rowId)){
      delete this.rows[rowId]
    }

    rowContainer.remove()
  }

  initRowChangeHandlers() {
    const isRowContainer = ($el) => $el.hasClass('acf-row') && $el.parents(`div.acf-field[data-name="${this.fieldName}"]`).length

    window.acf.addAction('append', (function ($el) {
      if ( isRowContainer($el) ) {
        this.initRow($el)
      }
    }).bind(this));

    window.acf.addAction('remove', (function ($el) {
      if ( isRowContainer($el) ) {
        this.deleteRow($el)
      }
    }).bind(this));

  }

  getHeaderContainer() {
    return this.Container.find('thead tr')
  }

  initHeader() {
    this.headerRow = new RepeaterFieldHeaderRow(this.getHeaderContainer(), this.fieldName, this.cellsConfig)
  }

  initLookupSuggestion(value, preview, note) {
    this.LookupSuggestion = LookupSuggestion(preview, note, (e) => {
      e.preventDefault()

      // Reset all rows
      this.deleteRows()

      // Track whether matching entities are provided for any row's cell, for the header cell's UI
      let cellsWithMatchingEntities = []

      const state = getState()

      for ( let [ i, row ] of value.entries() ) {
        const rowId = this.addRow()

        row.forEach(cell => {
          const { field_name: cellName, value: cellValue } = cell

          // Set the value to the lookup suggestion
          if (cellValue) {
            state[this.fieldName].rows[rowId].cells[cellName].setValue(cellValue)
          }

          // If the lookup suggestion provides matching entities for the cell, and UI to the body cell
          if (cell.hasOwnProperty("matched_entities") && cell.matched_entities.length){
            cellsWithMatchingEntities.push(cellName)
            state[this.fieldName].rows[rowId].cells[cellName].initMatchedEntities(cell.matched_entities)
          }
        })
      }

      // Add header cell UI if lookup provided any matching entities
      [...new Set(cellsWithMatchingEntities)].forEach(cellName => {
        this.headerRow.cells[cellName].initSelectMatchedEntities()
      })

    })
    this.Container.append(this.LookupSuggestion)
  }
}

export default AcfRepeaterField