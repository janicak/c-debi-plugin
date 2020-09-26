import { loaderClass } from "../constants"

const $ = window.jQuery

const Loader = () => $(`<span class="${loaderClass}"></span>`)

export default Loader