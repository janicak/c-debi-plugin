import RepeaterFieldBodyCell from "./BodyCell"
import EditEntityButton from "../EditEntityButton"
import ToastError from "../ToastError"
import { getState } from "../../state"
import { generateCreateEntityRequests } from "./helpers"
import { wp_fetch } from "../../utilities"
import { addSelect2Option } from "../helpers"
import { extraMarkupClass, hasEditButtonClass, createNewEntityClass, matchedEntitiesClass, matchedEntityClass } from "../../constants"

const $ = window.jQuery

class RepeaterFieldBodyCellEntityField extends RepeaterFieldBodyCell {
  value
  preview
  Select
  EditEntity
  CreateNewEntity
  MatchedEntities

  constructor(Container, fieldName, rowId, cellType, cellName, cellConfig){
    super(Container, fieldName, rowId, cellType, cellName, cellConfig)
    this.initSelect()
    this.initEditEntity()
    if (cellConfig.hasOwnProperty("createNewEntities")){
      this.initCreateNewEntity(this.cellConfig.createNewEntities)
    }
    this.value = this.getValue()
    this.preview = this.getPreview()
  }

  initSelect(){
    this.Select = this.Container.find("select")
      .on('select2:select', e => {
        const {id, text} = e.params.data
        this.value = id
        this.preview = text
        this.updateEditEntity()
      })
  }

  getValue(){
    return this.Select.find("option").val()
  }

  getPreview() {
    return this.Select.find("option").text()
  }

  setValue(value, preview) {
    addSelect2Option(this.Select, value, preview)
  }

  initEditEntity(){
    this.EditEntity = EditEntityButton(this.cellType, this.value, this.preview)
    this.Container.children('div.acf-input')
    .addClass(hasEditButtonClass)
    .append(this.EditEntity)
  }

  updateEditEntity(){
    this.EditEntity.remove()
    this.EditEntity = this.initEditEntity()
  }

  initCreateNewEntity(config){
    if (!this.CreateNewEntity){
      this.CreateNewEntity = CreateNewEntityUI(e => {
        e.preventDefault()

        const Trigger = $(e.target)

        if (!Trigger.isLoading()){
          Trigger.startLoading()

          const state = getState()
          const thisRow = state[this.fieldName].rows[this.rowId]
          const { method, reqs, rowsMissingRequiredFields } = generateCreateEntityRequests(config, [ thisRow ])

          if (rowsMissingRequiredFields){
            Trigger.endLoading()
            ToastError(`Missing required fields: "${reqs[0].missingRequiredFields.join(`," "`)}"`)

          } else {
            wp_fetch(method, reqs).then(res => {
              Trigger.endLoading()

              if ( !res.success ) {
                ToastError(`Request error: ${res.data}`)

              } else {
                const data = res.data[this.rowId]

                if (data.hasOwnProperty("error")){
                  ToastError(`Request error (row ${this.rowId}): ${res.data}`)

                } else {
                  this.setValue(data.id, data.text)
                }
              }
            })
          }
        }
      })

      this.Container.append(this.CreateNewEntity)
    }
  }

  initMatchedEntities(matchedEntities){
    if (!this.MatchedEntities){
      this.MatchedEntities = MatchedEntitiesUI(matchedEntities, e => {
        e.preventDefault()
        const Trigger = $(e.target)
        this.setValue(Trigger.data("id"), Trigger.data("text"))
      })
      this.Container.append(this.MatchedEntities)
    }
  }
}

const CreateNewEntityUI = (clickHandler) => (
  $(
    `<div class="${extraMarkupClass} ${createNewEntityClass}">
        <a href="#">Create and select a new entity</a>
    </div>`
  )
    .on("click", clickHandler)
)

const MatchedEntitiesUI = (matchedEntities, clickHandler) => {
  const UI = $(`<div class="${extraMarkupClass} ${matchedEntitiesClass}"><label>Matched entities: </label></div>`)

  const List = $(`<ul></ul>`)
  UI.append(List)

  matchedEntities.forEach(entity => {
    let { id, text } = entity
    text = `${text} [ID: ${id}]`
    List.append(
      $(`<li class="${matchedEntityClass}"><a href="#" data-text="${text}" data-id="${id}">Select ${text}</a></li>`)
    )
  })

  UI.find(`.${matchedEntityClass} a`).on("click", clickHandler)

  UI.on("select:first", e => {
    $(e.target).find(`.${matchedEntityClass}`).first().children('a').trigger("click")
  })

  return UI
}

export default RepeaterFieldBodyCellEntityField