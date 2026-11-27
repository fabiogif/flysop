import AsyncStorage from '@react-native-async-storage/async-storage';
import { getGuestProtocols, saveGuestProtocol } from '../../src/services/guestProtocols';

jest.mock('@react-native-async-storage/async-storage', () => ({
  getItem: jest.fn(),
  setItem: jest.fn(),
}));

describe('guestProtocols', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('converte protocolos salvos pela versão anterior (só string) para objeto sem código', async () => {
    (AsyncStorage.getItem as jest.Mock).mockResolvedValueOnce(
      JSON.stringify(['OC-2026-00001', { protocol: 'OC-2026-00002', code: 'K7QM-3XPR' }])
    );

    expect(await getGuestProtocols()).toEqual([
      { protocol: 'OC-2026-00001' },
      { protocol: 'OC-2026-00002', code: 'K7QM-3XPR' },
    ]);
  });

  it('salva o código junto e substitui o mesmo protocolo em vez de duplicar', async () => {
    (AsyncStorage.getItem as jest.Mock).mockResolvedValueOnce(JSON.stringify(['OC-2026-00001']));

    await saveGuestProtocol('OC-2026-00001', 'K7QM-3XPR');

    expect(AsyncStorage.setItem).toHaveBeenCalledWith(
      'guest_protocols',
      JSON.stringify([{ protocol: 'OC-2026-00001', code: 'K7QM-3XPR' }])
    );
  });
});
