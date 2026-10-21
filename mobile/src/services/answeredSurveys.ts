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

export const saveAnsweredSurvey = async (token: string): Promise<void> => {
  const current = await getAnsweredSurveys();
  if (current.includes(token)) return;
  await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify([token, ...current]));
};
