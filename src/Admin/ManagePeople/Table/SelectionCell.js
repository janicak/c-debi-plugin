import React, { forwardRef, useRef, useEffect } from "react"

const SelectionCell = forwardRef(
  ({ indeterminate, selectedRowIds, ...rest }, ref) => {
    const defaultRef = useRef()
    const resolvedRef = ref || defaultRef

    useEffect(() => {
      resolvedRef.current.indeterminate = indeterminate
    }, [resolvedRef, indeterminate])

    /*if (resolvedRef?.current){
      if (Object.keys(selectedRowIds).length  >= 2 && !resolvedRef.current?.checked ) {
        resolvedRef.current.disabled = true
      } else if (resolvedRef.current?.disabled) {
        resolvedRef.current.disabled = false
      }
    }*/

    return (
      <>
        <input type="checkbox" ref={resolvedRef} {...rest} />
      </>
    )
  }
)

export default SelectionCell