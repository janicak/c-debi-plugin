import ToastError from "./ToastError"
import { baseUrl, extraMarkupClass, editEntityButtonClass } from "../constants"

const $ = window.jQuery

const EditEntityButton = (cellType = null, id = null, text = null) => {

  let classNames = `${editEntityButtonClass} disabled`
  let href = "#"
  let title = "Edit the selected entity (none selected)"

  if (cellType && id){
    classNames = editEntityButtonClass
    href  = `${baseUrl}/wp-admin`
    switch (cellType) {
      case "post_object": default:
        href += `/post.php?post=${id}&action=edit`
        break;
    }
    if (text){
      title = `Edit ${text}`
    }
  }

  const Button = $(`
    <a class="${classNames} ${extraMarkupClass}" title="${title}" href="${href}" target="_blank">
      <span class="screen-reader-text">Edit</span>
      <span class="${editEntityButtonClass}-icon-bg">
        <span class="${editEntityButtonClass}-icon"></span>
      </span>
    </a>`
  )

  if (!cellType || !id){
    Button.on("click", e => {
      e.preventDefault()
      ToastError("No entity selected to edit.")
    })
  }

  return Button

}

export default EditEntityButton