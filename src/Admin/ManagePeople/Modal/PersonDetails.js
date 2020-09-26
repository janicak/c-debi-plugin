import React, { useReducer, useContext } from "react"
import { useQuery } from "react-query"
import styled from "styled-components"
import { useToasts } from "react-toast-notifications"

import Loader from "../Loader"
import { entityToChar } from "../helpers"
import { mergePeople, fetchPersonEntities } from "../requests"
import { AppContext } from '../App'

const StyledDiv = styled.div`
  .people .person:first-of-type {
    &:not(:only-child){
      margin-bottom: 20px;
      padding-bottom: 20px;
      border-bottom: 1px solid ${props => props.theme.darkBorderColor};
    }
  }
  h3 {
    margin-top: 0;
    margin-bottom: 10px;
    display: inline-block;
  }
  .keep-selected {
    display: inline-block;
    margin-left: 10px;
    input {
      position: relative;
      top: 1.5px;
    }
    label {
      position: relative;
      top: -1px;
    }
  }
  h4 {
    margin-top: 0;
    margin-bottom: 0.5rem;
  }
  ol {
    margin-top: 0;
    margin-left: 1.5rem;
    &:last-of-type {
      margin-bottom: 0
    }
  }
  .merge-people {
    margin-top: 20px;
    button {
     margin-right: 6px;
    }
  }
`

function reducer(state, action){
  switch(action.type) {
    case "start_merge": return {
      ...state,
      merging: true
    };
    case "end_merge": return {
      ...state,
      rows: action.payload,
      merging: false
    };
    case "select_row": return {
      ...state,
      selected: action.payload === state.selected ?  null : action.payload
    };
    default:
      throw new Error();
  }
}
const PersonDetails = ({ rows }) => {
  const [state, dispatch] = useReducer(reducer, {
    rows: rows,
    selected: null,
    merging: false
  })
  const { status, data, error } = useQuery([ "people", state.rows.map(r => r.ID) ], fetchPersonEntities)
  const {data: tableData, setData: setTableData} = useContext(AppContext)
  const { addToast } = useToasts()

  const handleMergeClick = async () => {
    dispatch({type: "start_merge"})

    // Merge people server-side
    const {deleted} = await mergePeople({
      from: state.rows.filter(r => r.ID !== state.selected).map(r => r.ID)[0],
      to: state.selected
    })

    // Update this view
    let newRows = state.rows.filter(r => deleted.indexOf(r.ID) === -1)
    dispatch({type: "end_merge", payload: newRows})

    // Update the table
    let newTableData = tableData.filter(r => deleted.indexOf(r.ID) === -1 )
    setTableData(newTableData)

    addToast("Merged People", { appearance: "success" })
  }

  return (
    <StyledDiv>
      <div className="people">
        { state.rows.map(r => (
          <div className="person" key={r.ID}>
            <h3>{r.post_title} [ID: {r.ID}]</h3>
              { status === 'loading' ? (
                <div><Loader /></div>
                ) : status === 'error' ? (
                <span>Error: {error.message}</span>
                ) : (
                  <>
                    { state.rows.length > 1 &&
                      <div className="keep-selected">
                        <input type="checkbox" disabled={ state.selected ? state.selected !== r.ID : false} onClick={() => dispatch({type: "select_row", payload: r.ID})} />
                        <label htmlFor="select">Keep</label>
                      </div>
                    }
                    { data[r.ID].map(postType => (
                        <div key={postType.label}>
                          <h4>{postType.label}</h4>
                          <ol>
                            { postType.posts.map(post => (
                              <li key={post.ID}>
                                <a href={entityToChar(post.permalink)} target="_blank">{post.post_title}</a> [ <a href={entityToChar(post.edit_link)} target="_blank">Edit</a> ]
                              </li>
                            ))}
                          </ol>
                        </div>
                      ))
                    }
                </>)
              }
          </div>
        ))}
      </div>
      {
        state.rows.length > 1 &&
          <div className="merge-people">
            <button className={`button action${state.selected ? "" : " disabled"}`} onClick={handleMergeClick}>Merge People</button>
            { state.merging && <Loader /> }
          </div>
      }
    </StyledDiv>
  )
}

export default PersonDetails