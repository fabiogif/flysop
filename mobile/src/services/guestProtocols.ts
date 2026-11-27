import AsyncStorage from '@react-native-async-storage/async-storage';

// Quem registra sem cadastro acompanha pelo protocolo + código de acompanhamento (o protocolo é
// sequencial; sem o código a API não mostra a ocorrência): guardados só neste aparelho
const STORAGE_KEY = 'guest_protocols';

export interface GuestProtocol {
  protocol: string;
  code?: string;
}

export const getGuestProtocols = async (): Promise<GuestProtocol[]> => {
  try {
    const raw = await AsyncStorage.getItem(STORAGE_KEY);
    const saved = raw ? (JSON.parse(raw) as (string | GuestProtocol)[]) : [];
    // Versões anteriores salvavam só o protocolo (string)
    return saved.map((item) => (typeof item === 'string' ? { protocol: item } : item));
  } catch {
    return [];
  }
};

export const saveGuestProtocol = async (protocol: string, code?: string): Promise<void> => {
  const current = (await getGuestProtocols()).filter((item) => item.protocol !== protocol);
  await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify([{ protocol, code }, ...current]));
};
