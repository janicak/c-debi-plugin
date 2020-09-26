import RepeaterFieldHeaderCell from "./HeaderCell"
import ToastError from "../ToastError"
import { getState } from "../../state"
import { generateCreateEntityRequests } from "./helpers"
import { wp_fetch } from "../../utilities"
import { extraMarkupClass, createNewEntitiesClass, selectFirstMatchedEntitiesClass } from "../../constants"

const $ = window.jQuery

class RepeaterFieldHeaderCellEntityField extends RepeaterFieldHeaderCell {
  CreateNewEntities
  SelectMatchedEntities

  constructor(Container, fieldName, cellType, cellName, cellConfig){
    super(Container, fieldName, cellType, cellName, cellConfig)

    if (cellConfig.hasOwnProperty("createNewEntities")){
      this.initCreateNewEntities(this.cellConfig.createNewEntities)
    }
  }

  initCreateNewEntities(config){
    if (!this.CreateNewEntities){
      this.CreateNewEntities = CreateNewEntitiesUI(e => {
        e.preventDefault()
        const Trigger = $(e.target)

        // If request isn't already in progress...
        if (!Trigger.isLoading()){
          const unselectedRows = this.getUnselectedRows()

          if (!unselectedRows.length) {
            ToastError(`No unselected rows`)

          } else {
            // Add async loading UI
            Trigger.startLoading()

            const { method, reqs, rowsMissingRequiredFields } = generateCreateEntityRequests(config, unselectedRows)

            if (rowsMissingRequiredFields){
              Trigger.endLoading()
              ToastError(`Missing required fields on ${rowsMissingRequiredFields} row(s)`)

            } else {
              wp_fetch(method, reqs).then(res => {
                Trigger.endLoading()

                if ( !res.success ) {
                  ToastError(`Request error: ${res.data}`)

                } else {
                  for (const [rowId, item] of Object.entries(res.data)){

                    if (item.hasOwnProperty("error")){
                      ToastError(`Request error (row ${rowId}): ${item.data}`)

                    } else {
                      const state = getState()
                      state[this.fieldName].rows[rowId].cells[this.cellName]
                        .setValue(item.id, item.text)
                    }
                  }
                } // end if (res.success)
              }) // end wp_fetch.then
            } // end if (!rowsMissingRequiredFields)
          } // end if (unselectedRows.length)
        } // end if (!Trigger.isLoading())
      }) // end CreateNewEntitiesUI clickHandler

      this.Container.append(this.CreateNewEntities)

    } // end if (!this.CreateNewEntities)
  }

  initSelectMatchedEntities(){
    if (!this.SelectMatchedEntities) {
      this.SelectMatchedEntities = SelectMatchedEntitiesUI(e => {
        e.preventDefault()

        this.getUnselectedRows().forEach(row => {

          if ( row.cells[this.cellName].MatchedEntities ) {
            row.cells[this.cellName].MatchedEntities.trigger("select:first")
          }
        })
      })

      this.Container.append(this.SelectMatchedEntities)
    }
  }

  getUnselectedRows() {
    const state = getState()
    return Object.entries(state[this.fieldName].rows).reduce((acc, [rowId, row]) => {

      if (!row.cells[this.cellName].getValue()){
        acc.push(row)
      }

      return acc

    }, [])
  }

}

const CreateNewEntitiesUI = (clickHandler) => {
  const UI = $(
    `<div class="${extraMarkupClass} ${createNewEntitiesClass}">
      <a href="#">Create and select a new entity for unselected rows</a>
     </div>`
  )

  UI.children('a').on("click", clickHandler)

  return UI
}

const SelectMatchedEntitiesUI = (clickHandler) => {
  const UI = $(
    `<div class="${extraMarkupClass} ${selectFirstMatchedEntitiesClass}">
      <a href="#">Select the first matched entity for all unselected rows</a>
    </div>`
  )

  UI.children('a').on("click", clickHandler)

  return UI
}

export default RepeaterFieldHeaderCellEntityField