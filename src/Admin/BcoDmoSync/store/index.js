import { configureStore } from '@reduxjs/toolkit'

import rootReducer from './reducers'

function configureAppStore() {
  const store = configureStore({
    reducer: rootReducer,
  })

  if (process.env.NODE_ENV !== 'production' && module.hot) {
    module.hot.accept('./reducers', () => store.replaceReducer(rootReducer))
  }

  return store
}

export default configureAppStore()