import { createAsyncThunk, createSlice } from '@reduxjs/toolkit'
import { triggerSync, triggerGetSyncStatus, triggerLinkPeople } from "../../utilities"

export const syncEntities = createAsyncThunk(
  'form/syncEntities',
  (args, thunkAPI) => {
    return new Promise((resolve, reject) => {
      triggerSync(args).then(data => {
        resolve(data)
      }, error => {
        reject(error)
      })
      window.setTimeout(() => {
        thunkAPI.dispatch(getSyncStatus())
      }, 2000)
    })
  }
)

const getSyncStatus = createAsyncThunk(
  'form/getSyncStatus',
  async (args, thunkAPI) => {
    const response = await triggerGetSyncStatus()
    window.setTimeout(() => {
      if (thunkAPI.getState().form.loading){
        thunkAPI.dispatch(getSyncStatus())
      }
    }, 2000)
    return response
  }
)

export const linkPeople = createAsyncThunk(
  'form/linkPeople',
  (args) => ( triggerLinkPeople(args) )
)

const formSlice = createSlice({
  name: 'form',
  initialState: {
    loading: false,
    syncStatusMessage: '',
    syncStatusPercentage: 0,
    unlinkedPeople: {},
    linkPeopleStatusMessage: ''
  },
  reducers: {},
  extraReducers: {
    [syncEntities.pending]: (state, action) => {
      console.log(action)
      state.loading = true
      state.syncStatusMessage = "Starting BCO-DMO update"
      state.syncStatusPercentage = 0
      state.unlinkedPeople = {}
      state.linkPeopleStatusMessage  = ''
    },
    [getSyncStatus.pending]: (state, action) => {
      console.log(action)
    },
    [getSyncStatus.rejected]: (state, action) => {
      console.log(action)
    },
    [getSyncStatus.fulfilled]: (state, action) => {
      if (state.loading){
        console.log(action)
        state.syncStatusMessage = action.payload.statusMessage
        state.syncStatusPercentage = action.payload.statusPercentage
      }
    },
    [syncEntities.rejected]: (state, action) => {
      console.log(action)
      state.loading = false
      state.syncStatusMessage = `Error: ${action.payload}`
      state.syncStatusPercentage = 0
    },
    [syncEntities.fulfilled]: (state, action) => {
      console.log(action)
      state.loading = false
      state.syncStatusPercentage = 100

      state.syncStatusMessage = "Completed. "

      if (action.payload.hasOwnProperty('datasets') && action.payload.hasOwnProperty('data_projects')){
        const { datasets, data_projects } = action.payload
        state.syncStatusMessage += ` 
          Datasets: ${datasets.created} created, ${datasets.updated} updated;
          Data Projects: ${data_projects.created} created, ${data_projects.updated} updated. `
      }

      if (action.payload.hasOwnProperty('unlinked_people')){
        const { unlinked_people } = action.payload
        state.unlinkedPeople = unlinked_people
        const unlinked_people_count = Object.keys(unlinked_people).length
        state.syncStatusMessage += `${unlinked_people_count} unlinked ${unlinked_people_count === 1 ? 'person' : 'people'} returned. `
      }

    },
    [linkPeople.pending]: (state, action) => {
      console.log(action)
      state.loading = true
      state.linkPeopleStatusMessage = "Updating"
    },
    [linkPeople.rejected]: (state, action) => {
      console.log(action)
      state.loading = false
      state.linkPeopleStatusMessage = `Error: ${action.payload}`
    },
    [linkPeople.fulfilled]: (state, action) => {
      console.log(action)
      state.loading = false
      const { rows_updated, people_created } = action.payload;
      state.linkPeopleStatusMessage = `Completed. 
        ${rows_updated} ${ parseInt(people_created) === 1 ? 'row' : 'rows' } updated; 
        ${people_created} ${ parseInt(people_created) === 1 ? 'person' : 'people' } created.`
    },
  }
})

export default formSlice.reducer