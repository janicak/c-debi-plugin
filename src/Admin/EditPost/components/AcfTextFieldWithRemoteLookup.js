import AcfField from './AcfField'
import ToastError from "./ToastError"
import { getState } from "../state"
import { wp_fetch } from "../utilities"
import { extraMarkupClass, lookupTriggerClass, lookupSuggestionClass } from "../constants"

const $ = window.jQuery

export class AcfTextFieldWithRemoteLookup extends AcfField {
  Input
  LookupTrigger
  value

  constructor(fieldName, lookupConfig){
    super(fieldName)
    this.initInput()
    this.initLookupButton(lookupConfig)
    this.value = this.getValue()
  }

  initInput(){
    this.Input = this.Container.find("input")
  }

  getValue() {
    return this.Input.val()
  }

  initLookupButton(lookupConfig){
    const { buttonLabel, lookupMethod } = lookupConfig
    this.LookupTrigger = LookupButton(buttonLabel, e => {
      e.preventDefault()

      const Trigger = $(e.target)

      if ( !Trigger.isLoading() ) {
        const lookupValue = this.getValue()

        if ( lookupValue ) {
          const state = getState()

          // Check cached query value for development
          if ( state.hasOwnProperty('cachedLookupQuery') ) {
            this.initLookupSuggestions(state.cachedLookupQuery)

          } else {
            Trigger.startLoading()

            wp_fetch(lookupMethod, { id: lookupValue }).then(res => {
              Trigger.endLoading()

              if ( !res.success ) {
                ToastError(`Error: ${res.data}`)

              } else {

                // cache the response for development
                if (process.env.DEBUG){
                  state.cachedLookupQuery = res.data;
                }

                // Add lookup suggestions to the fields specified in the server response
                this.initLookupSuggestions(res.data)

              }
            })
          }
        } else {
          ToastError("No value submitted.")
        }
      }
    })
    this.Container
      .addClass(lookupTriggerClass)
      .children('.acf-input-wrap').append(this.LookupTrigger)
  }

  initLookupSuggestions(suggestions){
    const state = getState()
    $(`.${lookupSuggestionClass}`).remove()
    suggestions.forEach(suggestion => {
      let { field_name, value, preview, note } = suggestion
      preview = preview ? preview : value

      state[field_name].initLookupSuggestion(value, preview, note)
    })
  }
}

const LookupButton = (label, clickHandler) => (
  $(`<button class="button button-primary ${extraMarkupClass}">${label}</button>`)
    .on("click", clickHandler)
)

export default AcfTextFieldWithRemoteLookup