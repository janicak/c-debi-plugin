import Loader from "./components/Loader"
import { disabledClass, loaderClass } from "./constants"

const $ = window.jQuery
const { ajax_url, action, nonce, route } = window.c_debi_plugin

export const wp_fetch = (method, args) => (
  new Promise((resolve, reject) => {
    $.ajax({
      type: "POST",
      url: ajax_url,
      data: { action, nonce, route, reqs: [{ method, args }] },
      success: (res, textStatus, jqXHR) =>{
        resolve(res);
      },
      error: (jqXHR, textStatus, errorThrown) => {
        reject(errorThrown);
      }
    })
  })
)

export const extendJqueryPrototype = () => {
  $.fn.extend({
    isLoading: function(){
      return this.hasClass(disabledClass)
    },
    startLoading: function() {
      this.addClass(disabledClass)
      this.append(Loader())
    },
    endLoading: function() {
      this.removeClass(disabledClass)
      this.find(`.${loaderClass}`).remove()
    }
  })
}