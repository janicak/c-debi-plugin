import axios from "axios"
import qs from "qs"
import { queryCache } from "react-query"

const wp_ajax_fetch = ( reqs ) => {
  const { ajax_url, action, nonce, route } = window.c_debi_plugin;

  return new Promise((resolve, reject) => {
    axios({
      method: 'POST',
      url: ajax_url,
      headers: { 'content-type': 'application/x-www-form-urlencoded' },
      data: qs.stringify({
        action,
        nonce,
        route,
        reqs
      })
    }).then(res => {
      if (res.data.success) {
        resolve(res.data.data)
      } else {
        reject(res.data.data)
      }
    })
    .catch(error => reject(error))
  })

}

export const deletePeople = (IDs) => (
  wp_ajax_fetch([{
    method: "delete_people",
    args: {IDs: IDs },
  }])
)

export const mergePeople = ({from, to}) => (
  wp_ajax_fetch([{
    method: "merge_people",
    args: {from, to},
  }])
  .then(data => {
    for (let [ID, value] of Object.entries(data.updated)) {
      ID = parseInt(ID)
      queryCache.prefetchQuery(["people", [ID]], () => ({ [ID]: value } ) )
    }
    return data
  })
)

export const fetchPersonEntities = (key, IDs) => {
  const { cachedQueries, uncachedQueries } = IDs.reduce((acc, ID) => {
    const queryData = queryCache.getQueryData([ "people", [ ID ] ])
    if ( queryData ) {
      acc.cachedQueries[ID] = queryData
    } else {
      acc.uncachedQueries.push(ID)
    }
    return acc
  }, { cachedQueries: {}, uncachedQueries: [] })

  return new Promise((resolve, reject) => {
    if ( uncachedQueries.length ) {
      wp_ajax_fetch(uncachedQueries.map(ID => ({
        method: "get_person_entities",
        args: ID,
        key: ID
      }))).then(data => {
        for ( let [ ID, value ] of Object.entries(data) ) {
          ID = parseInt(ID)
          if ( ID ) {
            queryCache.prefetchQuery([ "people", [ ID ] ], () => ({ [ID]: value } ))
          }
        }
        if ( Object.keys(cachedQueries).length ) {
          for ( let [ ID, value ] of Object.entries(cachedQueries) ) {
            data[ID] = value[ID]
          }
        }
        resolve(data)
      })
    } else {
      let data = {}
      if ( Object.keys(cachedQueries).length ) {
        for ( let [ ID, value ] of Object.entries(cachedQueries) ) {
          data[ID] = value[ID]
        }
      }
      resolve(data)
    }
  })
}

export const resetAndCreateTestData = () => (
  wp_ajax_fetch([{
    method: "reset_test_data"
  }])
)

export const removeTestData = () => (
  wp_ajax_fetch([{
    method: "remove_test_data"
  }])
)
