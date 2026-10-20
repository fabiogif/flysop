import AsyncStorage from '@react-native-async-storage/async-storage';

// Quem registra sem cadastro acompanha pelo protocolo: guardado só neste aparelho
const STORAGE_KEY = 'guest_protocols';

export const getGuestProtocols = async (): Promise<string[]> => {
  try {
    const raw = await AsyncStorage.getItem(STORAGE_KEY);
    return raw ? (JSON.parse(raw) as string[]) : [];
  } catch {
    return [];
  }
};

export const saveGuestProtocol = async (protocol: string): Promise<void> => {
  const current = await getGuestProtocols();
  if (current.includes(protocol)) return;
  await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify([protocol, ...current]));
};
