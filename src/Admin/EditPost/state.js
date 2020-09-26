import PostTitle from './components/PostTitle'
import AcfTextFieldWithRemoteLookup from './components/AcfTextFieldWithRemoteLookup'
import AcfTextField from './components/AcfTextField'
import AcfRadioSelectField from './components/AcfRadioSelectField'
import AcfDatePickerField from './components/AcfDatePickerField'
import AcfRepeaterField from './components/AcfRepeaterField'

export const getState = () => {
  return window.c_debi_plugin.state
}

const initState = (post_type) => {
  switch ( post_type ) {
    case "publication":
    default: {
      window.c_debi_plugin.state = initPubState()
    }
  }
}

const initPubState = () => {

  const crossrefToPersonConfig = {
    method: "create_new_entities",
    args: {
      sourceEntity: "crossrefPublication",
      targetEntity: "person",
      fieldMap: [
        {
          sourceField: "given",
          targetField: "first",
          required: false
        },
        {
          sourceField: "family",
          targetField: "last",
          required: true
        },
      ]
    }
  }

  return {
    post_title: new PostTitle(),
    publication_doi: new AcfTextFieldWithRemoteLookup("publication_doi",
      { buttonLabel: "Query CrossRef for field suggestions", lookupMethod: "crossref_fetch" }
    ),
    publication_contribution_number: new AcfTextField("publication_contribution_number"),
    publication_url: new AcfTextField("publication_url"),
    publication_type: new AcfRadioSelectField("publication_type"),
    publication_publisher_title: new AcfTextField("publication_publisher_title"),
    publication_date_published: new AcfDatePickerField("publication_date_published"),
    publication_authors: new AcfRepeaterField("publication_authors", {
      person: { createNewEntities: crossrefToPersonConfig }
    }),
    publication_editors: new AcfRepeaterField("publication_editors", {
      person: { createNewEntities: crossrefToPersonConfig }
    })
  }
}

export default initState