import React, { useState } from 'react'
import styled from 'styled-components'
import { useSelector, useDispatch } from "react-redux"
import { useForm } from 'react-hook-form'
import { syncEntities } from "./formSlice"
import FormSection from "./FormSection"
import LoadingEllipsis from "../../sharedComponents/LoadingEllipsis"

const StyledSyncForm = styled.form`
  & > label {
    font-weight: bold;
  }
  .options {
    margin-top: 4px;
    display: flex;
    flex-direction: column;
    label {
      margin-right: 8px;
      input {
        position: relative;
        top: 1px;
      }
    }
    .subOptions {
      margin-left: 19px;
      margin-top: 2px;
      margin-bottom: 5px;
    }
  }
  input.button-primary {
    margin-top: 12px;
    width: 150px;
  }
`
const SyncForm = () => {
  const { loading } = useSelector(state => state.form)
  const dispatch = useDispatch()
  const { register, handleSubmit, getValues } = useForm()
  const [ formDisabled, setFormDisabled ] = useState(loading)
  const [ triggerSyncSelected, setTriggerSyncSelected ] = useState(true)

  const onInputChange = () => {
    const formValues = getValues()
    setTriggerSyncSelected(formValues.trigger_sync)

    const anyChecked = formValues.trigger_sync || formValues.find_unlinked_people
    setFormDisabled(!anyChecked || loading)
  }

  const onFormSubmit = data =>{
    dispatch(syncEntities(data))
  }

  return(
    <StyledSyncForm onSubmit={handleSubmit(onFormSubmit)}>
      <label>Options: </label>
      <div className="options">
        <label>
          <input type="checkbox" ref={register} name="trigger_sync" defaultChecked  onChange={() => onInputChange() }/>
          Update / create local Datasets, Projects
          <div className={`subOptions${triggerSyncSelected ? '' : ' hidden'}`}>
            <label>
              <input type="radio" ref={register} name="use_cache" value="false" defaultChecked />
              Fetch latest metadata from remote server
            </label>
            <label>
              <input type="radio" ref={register} name="use_cache" value="true" />
              Used last cached response from remote server
            </label>
          </div>
        </label>
        <label>
          <input type="checkbox" ref={register} name="find_unlinked_people" defaultChecked onChange={() => onInputChange() }/>
          Link remote metadata with local Person entities, and return form to manually pair novel entries
        </label>
      </div>
      <input className="button-primary" type="submit" value="Submit" disabled={formDisabled} />
    </StyledSyncForm>
  )
}

const StyledSyncStatusIndicator = styled.div`
  margin-top: 12px;
  label {
    font-weight: bold;
  }
  .progress-bar {
    height: 15px;
    width: 100%;
    margin-top: 4px;
    box-shadow: inset 0.75px 0.5px 4px 0px #4c4c4cc2;
    .progress-bar-fill {
      background: #007cba87;
      height: 100%;
      transition: width .5s ease-in;
    }
  }
  .status-message {
    margin-top: 3px;
    .message {
      margin-right: 3px;
      font-style: italic;
    }
  } 
`
const SyncStatusIndicator = () => {
  const {
    loading,
    syncStatusMessage: statusMessage,
    syncStatusPercentage: statusPercentage
  } = useSelector(state => state.form)

  return (
    <StyledSyncStatusIndicator>
      <label>Progress: </label>
      <div className="progress-bar">
        <div className="progress-bar-fill" style={{width: `${statusPercentage}%`}}/>
      </div>
      <div className="status-message">
        <span className="message">{statusMessage}</span>
        { loading && <LoadingEllipsis />}
      </div>
    </StyledSyncStatusIndicator>
  )
}

const Sync = () => (
  <FormSection heading={'Part 1: Reconcile remote (BCO-DMO) and local (website) metadata'}>
    <SyncForm />
    <SyncStatusIndicator />
  </FormSection>
)

export default Sync