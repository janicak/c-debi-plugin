import { extraMarkupClass, lookupSuggestionClass } from "../constants"

const $ = window.jQuery

const LookupSuggestion = (text, note, clickHandler) => {
  const Suggestion = $(`<div class="${extraMarkupClass} ${lookupSuggestionClass}">Use <a href='#'>${text}</a></div>`)

  if (note) {
    Suggestion.append(`<div class="note">${note}</div>`)
  }

  Suggestion.children('a').on("click", clickHandler)

  return Suggestion
}

export default LookupSuggestion