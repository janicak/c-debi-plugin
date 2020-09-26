const enrichTableData = (data) => {

  let lastNames = {}

  data.forEach((person, i) => {

    const lastName = person.person_last_name_normalized
    const firstName = person.person_first_name_normalized

    if (!lastNames.hasOwnProperty(lastName)){
      lastNames[lastName] = [];
    }

    lastNames[lastName].push({ rowIndex: i, firstName: firstName })
  })

  Object.entries(lastNames).forEach( ([lastName, persons]) => {
    if (persons.length > 1){
      let firstNames = {}
      persons.forEach( person => {
        data[person.rowIndex].nonUniqueLastName = "true";
        const firstName = person.firstName
        if (!firstNames.hasOwnProperty(firstName)){
          firstNames[firstName] = [];
        }
        firstNames[firstName].push(person.rowIndex)
        Object.entries(firstNames).forEach( ([firstName, personRowIndices]) => {
          if (personRowIndices.length > 1) {
            personRowIndices.forEach(personRowIndex => {
              data[personRowIndex].nonUniqueLastAndFirstName = "true"
            })
          } else {
            data[personRowIndices[0]].nonUniqueLastAndFirstName = "false"
          }
        })
      })
    } else {
      data[persons[0].rowIndex].nonUniqueLastName = "false"
      data[persons[0].rowIndex].nonUniqueLastAndFirstName = "false"
    }
  })

  return data
}

export default enrichTableData