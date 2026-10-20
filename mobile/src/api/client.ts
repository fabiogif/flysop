import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

export const API_BASE_URL = 'http://10.0.2.2:8010/api'; // 10.0.2.2 para emulador Android, localhost para iOS

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
  },
  timeout: 15000,
});

// Interceptor para injeção automática de Bearer Token
apiClient.interceptors.request.use(async (config) => {
  try {
    const token = await SecureStore.getItemAsync('auth_token');
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  } catch (error) {
    console.warn('Erro ao ler token do SecureStore', error);
  }
  return config;
});

/**
 * Mensagem amigável de um erro da API. Laravel 8 devolve `message` genérica em inglês
 * ("The given data was invalid."); a mensagem útil fica no primeiro item de `errors`.
 */
export const getApiErrorMessage = (err: unknown): string | null => {
  const data = axios.isAxiosError(err) ? err.response?.data : null;
  if (!data || typeof data !== 'object') return null;

  if ('errors' in data && data.errors && typeof data.errors === 'object') {
    const first = Object.values(data.errors as Record<string, string[]>)[0];
    if (Array.isArray(first) && first[0]) return first[0];
  }
  return 'message' in data && typeof data.message === 'string' ? data.message : null;
};

// Interceptor para tratamento de 401 (desautenticação)
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await SecureStore.deleteItemAsync('auth_token');
    }
    return Promise.reject(error);
  }
);
