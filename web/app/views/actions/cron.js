import fetcher from '~/helpers/fetcher'
import * as actionTypes from '~/constants/actions'

export function updateCron(cron) {
  const data = {
    cron: cron
  }

  return fetcher('cron/update', data, actionTypes.CRON_UPDATE)
}