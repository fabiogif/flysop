import AsyncStorage from '@react-native-async-storage/async-storage';
import { OfflineQueueService } from '../../src/services/offlineQueue';
jest.mock('@react-native-async-storage/async-storage', () => ({
  getItem: jest.fn(),
  setItem: jest.fn(),
}));

jest.mock('@react-native-community/netinfo', () => ({
  addEventListener: jest.fn(() => jest.fn()),
}));

jest.mock('expo-secure-store', () => ({
  getItemAsync: jest.fn(),
  setItemAsync: jest.fn(),
  deleteItemAsync: jest.fn(),
}));

describe('OfflineQueueService', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('retorna fila vazia quando não há dados salvos', async () => {
    (AsyncStorage.getItem as jest.Mock).mockResolvedValueOnce(null);

    const queue = await OfflineQueueService.getQueue();
    expect(queue).toEqual([]);
    expect(AsyncStorage.getItem).toHaveBeenCalledWith('@ciop_offline_occurrences_queue');
  });

  it('enfileira uma ocorrência pendente no AsyncStorage', async () => {
    (AsyncStorage.getItem as jest.Mock).mockResolvedValueOnce('[]');

    await OfflineQueueService.enqueue({
      title: 'Buraco no asfalto',
      type_occurrences_id: 1,
      latitude: -12.9714,
      longitude: -38.5014,
      address: 'Avenida Sete',
    });

    expect(AsyncStorage.setItem).toHaveBeenCalledWith(
      '@ciop_offline_occurrences_queue',
      expect.stringContaining('Buraco no asfalto')
    );
  });
});
