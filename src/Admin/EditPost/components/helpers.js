// see: https://select2.org/programmatic-control/add-select-clear-items#preselecting-options-in-an-remotely-sourced-ajax-select2
export const addSelect2Option = (Select, id, text) => {
  const newOption = new Option(text, id)
  Select.children("option").remove()
  Select.append(newOption).trigger('change')
  Select.trigger({ type: 'select2:select', params: { data: { id, text }, selected: true }})
}