import React, { useState } from 'react'
import styled from 'styled-components'
import FormSection from "./FormSection"
import { connect, useSelector } from "react-redux"
import { useForm } from 'react-hook-form'
import { linkPeople } from "./formSlice"
import LoadingEllipsis from "../../sharedComponents/LoadingEllipsis"

const StyledLinkPeople = styled.form`
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  table {
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    background: #e6e6e6;
    width: 100%;
    border-spacing: 0;
    thead tr th {
      height: 24px;
      background-color: white;
      text-align: left;
      &:first-child {
        min-width: 125px;
      }
    }
    td, th {
      padding: 8px 10px;
    }
    tbody {
     tr {
      &:nth-child(even) {
        background-color:white;
      }
      td {
        &:first-child {
          width:1%;
          white-space:nowrap;
        }
        select {
          width: 100%;
          max-width: unset;
        }
      }
     }
    }
  }
  input.button-primary {
    margin-top: 16px;
    width: 150px;
  }
  .status-message {
    margin-top: 3px;
    font-style: italic;
  }
`
const LinkPeople = ({ linkPeople }) => {
  const { unlinkedPeople, loading, linkPeopleStatusMessage } = useSelector(state => state.form)
  const { register, handleSubmit, getValues } = useForm()
  const [ formDisabled, setFormDisabled ] = useState(loading)

  const onFormSubmit = data =>{

    const args = Object.entries(unlinkedPeople).map(([bcoDmoName, person]) => {
      const {instances, parsed_name} = person
      const create_new = data[bcoDmoName] === 'create_new'
      const selected_entity = data[bcoDmoName] !== 'create_new' ? data[bcoDmoName] : null
      return {
          instances,
          selected_entity,
          create_new,
          parsed_name
        }
    })
    linkPeople(args).then(() => {
      setFormDisabled(false)
    })
    setFormDisabled(true)
  }

  return(
    <>
      {unlinkedPeople && Object.entries(unlinkedPeople).length ? (
        <FormSection heading={`Part 2: Link unpaired, BCO-DMO people with new or existing, local entities`}>
          <StyledLinkPeople onSubmit={handleSubmit(onFormSubmit)}>
            <table>
              <thead><tr><th>BCO-DMO people</th><th>Local entity matches</th></tr></thead>
              <tbody>
              {Object.entries(unlinkedPeople).map(([bcoDmoName, person]) => (
                <tr key={bcoDmoName}>
                  <td>{bcoDmoName}</td>
                  <td><select name={bcoDmoName} ref={register}>
                    { person.matched_person_posts.length && (
                      person.matched_person_posts.map(({ post_title: text, ID: value }) => (
                        <option key={value} value={value}>{text} [ID: {value}]</option>
                      ))
                    )}
                    <option value="create_new">Create new person: "{person.parsed_name.post_title}"</option>
                  </select></td>
                </tr>
              ))}
              </tbody>
            </table>
            <input className="button-primary" type="submit" value="Link People" disabled={formDisabled} />
            <div className="status-message">
              <span className="message">{linkPeopleStatusMessage}</span>
              { loading && <LoadingEllipsis />}
            </div>
          </StyledLinkPeople>
        </FormSection>
      ) : ''}
    </>
  )
}

export default connect(null, { linkPeople } )(LinkPeople)