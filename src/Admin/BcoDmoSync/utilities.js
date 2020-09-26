import axios from "axios"
import qs from "qs"

export const triggerSync = (args) => (
  wp_ajax_fetch([{
    method: 'trigger_bcodmo_sync',
    args
  }])
)

export const triggerGetSyncStatus = () => (
  wp_ajax_fetch([{ method: 'check_bcodmo_sync_status' }])
)

export const triggerLinkPeople = (args) => (
  wp_ajax_fetch([{
    method: 'trigger_link_people',
    args
  }])
)

const wp_ajax_fetch = ( reqs ) => {
  const { ajax_url, action, nonce, route } = window.c_debi_plugin

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