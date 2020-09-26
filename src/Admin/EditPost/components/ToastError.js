import Toastify from "toastify-js"
import 'toastify-js/src/toastify.css'

const ToastError = (message) => {
  Toastify({
    text: message,
    duration: 3000,
    close: true,
    gravity: "top", // `top` or `bottom`
    position: 'right', // `left`, `center` or `right`
    backgroundColor: "rgb(195, 33, 33)",
    stopOnFocus: true, // Prevents dismissing of toast on hover
    onClick: function(){} // Callback after click
  }).showToast();
}

export default ToastError