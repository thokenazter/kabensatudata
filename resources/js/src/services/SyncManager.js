import axios from 'axios'
import { OfflineManager } from './OfflineManager'

export const SyncManager = {
  async syncNow() {
    const queue = await OfflineManager.getQueue()
    if (!queue.length) return { ok: true, synced: 0 }
    try {
      await axios.post('/api/sync/changes', { changes: queue })
      await OfflineManager.clearQueue()
      await OfflineManager.setMeta('lastSync', new Date().toISOString())
      return { ok: true, synced: queue.length }
    } catch (e) {
      return { ok: false, error: e?.message || 'Sync failed' }
    }
  },
}

