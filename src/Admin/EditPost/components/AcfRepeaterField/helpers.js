export const generateCreateEntityRequests = (config, rows) => {
  const {
    method, args: { sourceEntity, targetEntity, fieldMap }
  } =  config

  const createEntityFetchInfo = {
    method,
    reqs: [],
    rowsMissingRequiredFields: 0
  }

  rows.forEach(row => {
    const req = {
      reqId: row.rowId,
      sourceEntity,
      targetEntity,
      fields: {},
      missingRequiredFields: []
    }

    fieldMap.forEach(mapping => {
      const { sourceField, targetField, required } = mapping
      const sourceValue = row.cells[sourceField].getValue()

      req.fields[targetField] = sourceValue

      if (required && !sourceValue){
        req.missingRequiredFields.push(sourceField)
      }
    })

    createEntityFetchInfo.reqs.push(req)
  })

  createEntityFetchInfo.rowsMissingRequiredFields = createEntityFetchInfo.reqs.reduce((acc, req) => {
    return acc + req.missingRequiredFields.length ? 1 : 0
  }, 0)

  return createEntityFetchInfo

}