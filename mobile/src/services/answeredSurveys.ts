import AsyncStorage from '@react-native-async-storage/async-storage';

// Pesquisas respondidas neste aparelho (a API não identifica quem respondeu): base do badge
const STORAGE_KEY = 'answered_surveys';

export const getAnsweredSurveys = async (): Promise<string[]> => {
  try {
    const raw = await AsyncStorage.getItem(STORAGE_KEY);
    return raw ? (JSON.parse(raw) as string[]) : [];
  } catch {
    return [];
  }
};

const DEVICE_ID_KEY = 'survey_device_id';

// Id aleatório do aparelho: a API usa (via HMAC) para barrar a 2ª resposta de quem responde sem login
export const getSurveyDeviceId = async (): Promise<string> => {
  const existing = await AsyncStorage.getItem(DEVICE_ID_KEY);
  if (existing) return existing;
  const id = `${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}-${Math.random().toString(36).slice(2)}`;
  await AsyncStorage.setItem(DEVICE_ID_KEY, id);
  return id;
};

export const saveAnsweredSurvey = async (token: string): Promise<void> => {
  const current = await getAnsweredSurveys();
  if (current.includes(token)) return;
  await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify([token, ...current]));
};
