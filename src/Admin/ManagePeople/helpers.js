export const entityToChar = str => {
  const textarea = document.createElement('textarea');
  textarea.innerHTML = str;
  return textarea.value;
}

export const getScrollBarWidth = () => {
  let outside = document.createElement("div")
  let inside = document.createElement("div")
  outside.style.width = inside.style.width = "100%"
  outside.style.overflow = "scroll"
  document.body.appendChild(outside).appendChild(inside)
  const scrollbar = outside.offsetWidth - inside.offsetWidth
  outside.parentNode.removeChild(outside)
  return scrollbar
}