import { postType, bodyClass, extraMarkupClass, lookupTriggerClass } from './constants'
import alterLayout from "./layout"
import initState from "./state"
import { extendJqueryPrototype } from "./utilities"
import './index.scss'

const $ = window.jQuery

$(document).ready(() => {
  extendJqueryPrototype()
  alterLayout(postType)
  init()
})

if (module.hot){
  module.hot.accept([
    './constants.js',
    './layout.js',
    './state.js',
    './utilities.js',
  ], function() {
    init()
    //$('.acf-field[data-name="publication_authors"] .field-suggestion a').trigger('click')
    $(`.${lookupTriggerClass} button`).trigger("click")
  })
}

function init() {
  if (['dataset', 'data_project', 'publication'].includes(postType)){
    $("body").addClass(bodyClass);
    $(`.${extraMarkupClass}`).remove()
    initState(postType)
  }
}







