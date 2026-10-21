import AsyncStorage from '@react-native-async-storage/async-storage';
import NetInfo from '@react-native-community/netinfo';
import { apiClient } from '../api/client';
import { saveGuestProtocol } from './guestProtocols';

export interface QueuedOccurrence {
  id: string;
  title: string;
  type_occurrences_id: number;
  description?: string;
  latitude?: number;
  longitude?: number;
  address?: string;
  neighborhood?: string;
  city?: string;
  state?: string;
  photoUri?: string;
  is_anonymous?: boolean;
  createdAt: string;
  attempts: number;
}

const STORAGE_KEY = '@ciop_offline_occurrences_queue';

export class OfflineQueueService {
  private static isProcessing = false;

  public static async enqueue(item: Omit<QueuedOccurrence, 'id' | 'createdAt' | 'attempts'>): Promise<void> {
    const queue = await this.getQueue();
    const queuedItem: QueuedOccurrence = {
      ...item,
      id: `${Date.now()}_${Math.random().toString(36).substring(2, 7)}`,
      createdAt: new Date().toISOString(),
      attempts: 0,
    };

    queue.push(queuedItem);
    await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify(queue));
  }

  public static async getQueue(): Promise<QueuedOccurrence[]> {
    try {
      const data = await AsyncStorage.getItem(STORAGE_KEY);
      if (!data) return [];
      const parsed = JSON.parse(data);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }

  public static async processQueue(): Promise<{ processed: number; failed: number }> {
    if (this.isProcessing) return { processed: 0, failed: 0 };
    this.isProcessing = true;

    const queue = await this.getQueue();
    if (queue.length === 0) {
      this.isProcessing = false;
      return { processed: 0, failed: 0 };
    }

    const remainingQueue: QueuedOccurrence[] = [];
    let processedCount = 0;
    let failedCount = 0;

    for (const item of queue) {
      try {
        const formData = new FormData();
        formData.append('title', item.title);
        formData.append('type_occurrences_id', String(item.type_occurrences_id));
        formData.append('lgpd_consent', '1');
        if (item.description) formData.append('description', item.description);
        if (item.latitude) formData.append('latitude', String(item.latitude));
        if (item.longitude) formData.append('longitude', String(item.longitude));
        if (item.address) formData.append('address', item.address);
        if (item.neighborhood) formData.append('neighborhood', item.neighborhood);
        if (item.city) formData.append('city', item.city);
        if (item.state) formData.append('state', item.state);
        if (item.is_anonymous) formData.append('is_anonymous', '1');

        if (item.photoUri) {
          const filename = item.photoUri.split('/').pop() ?? 'foto_offline.jpg';
          const match = /\.(\w+)$/.exec(filename);
          const type = match ? `image/${match[1]}` : 'image/jpeg';

          formData.append('anexo[]', {
            uri: item.photoUri,
            name: filename,
            type,
          } as unknown as Blob);
        }

        const response = await apiClient.post<{
          data?: { protocol?: string };
          tracking_code?: string;
        }>('/occurrences', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });

        // Sem conta: guarda protocolo + código, senão o autor perde o acompanhamento
        const sent = response?.data;
        if (sent?.tracking_code && sent.data?.protocol) {
          await saveGuestProtocol(sent.data.protocol, sent.tracking_code);
        }

        processedCount += 1;
      } catch {
        item.attempts += 1;
        remainingQueue.push(item);
        failedCount += 1;
      }
    }

    await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify(remainingQueue));
    this.isProcessing = false;
    return { processed: processedCount, failed: failedCount };
  }

  public static setupAutoSyncListener(): () => void {
    const unsubscribe = NetInfo.addEventListener((state) => {
      if (state.isConnected && state.isInternetReachable !== false) {
        this.processQueue();
      }
    });

    return unsubscribe;
  }
}
