import { combineReducers } from 'redux'
import formReducer from '../features/form/formSlice'

export default combineReducers({
  form: formReducer,
})
