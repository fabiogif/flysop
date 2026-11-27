import React, { createContext, useContext, useState, useEffect } from 'react';
import * as SecureStore from 'expo-secure-store';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { apiClient } from '../api/client';
import { Client, Manager } from '../types';

const GUEST_MODE_KEY = 'guest_mode';

export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
  phone?: string;
  terms_accepted: boolean;
  marketing_opt_in?: boolean;
}

interface AuthContextData {
  client: Client | null;
  manager: Manager | null;
  isAuthenticated: boolean;
  isGuest: boolean;
  isLoading: boolean;
  registerWithEmail: (data: RegisterPayload) => Promise<void>;
  loginWithEmail: (email: string, password: string) => Promise<void>;
  loginAsManager: (email: string, password: string) => Promise<void>;
  setManager: (manager: Manager) => void;
  enterAsGuest: () => void;
  logout: () => Promise<void>;
  refreshProfile: () => Promise<void>;
}

const AuthContext = createContext<AuthContextData>({} as AuthContextData);

type MeResponse = { data?: Client | Manager } & (Client | Manager);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [client, setClient] = useState<Client | null>(null);
  const [manager, setManager] = useState<Manager | null>(null);
  const [isGuest, setIsGuest] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // Modo anônimo fica salvo no aparelho: reabrir o app não volta para a tela de cadastro
  const setGuest = (value: boolean) => {
    setIsGuest(value);
    (value ? AsyncStorage.setItem(GUEST_MODE_KEY, '1') : AsyncStorage.removeItem(GUEST_MODE_KEY)).catch(() => {});
  };

  // /auth/me atende cidadão e gestor; "role" decide qual dos dois é
  const applyMe = (me: Client | Manager) => {
    if ('role' in me && me.role === 'manager') {
      setManager(me);
      setClient(null);
    } else {
      setClient(me as Client);
      setManager(null);
    }
  };

  useEffect(() => {
    (async () => {
      try {
        const token = await SecureStore.getItemAsync('auth_token');
        if (token) {
          const response = await apiClient.get<MeResponse>('/auth/me');
          applyMe(response.data.data ?? response.data);
        } else if ((await AsyncStorage.getItem(GUEST_MODE_KEY)) === '1') {
          setIsGuest(true);
        }
      } catch {
        await SecureStore.deleteItemAsync('auth_token');
      } finally {
        setIsLoading(false);
      }
    })();
  }, []);

  const registerWithEmail = async (data: RegisterPayload) => {
    const response = await apiClient.post<{ token: string; client: Client }>('/auth/register', {
      ...data,
      terms_accepted: data.terms_accepted ? 1 : 0,
    });
    const { token, client: clientData } = response.data;
    await SecureStore.setItemAsync('auth_token', token);
    setClient(clientData);
    setGuest(false);
  };

  const loginWithEmail = async (email: string, password: string) => {
    const response = await apiClient.post<{ token: string; client: Client }>('/sanctum/token', {
      email,
      password,
      device_name: 'ciop-app',
    });
    await SecureStore.setItemAsync('auth_token', response.data.token);
    setClient(response.data.client);
    setManager(null);
    setGuest(false);
  };

  const loginAsManager = async (email: string, password: string) => {
    const response = await apiClient.post<{ token: string; user: Manager }>('/auth/staff/login', {
      email,
      password,
      device_name: 'ciop-app',
    });
    await SecureStore.setItemAsync('auth_token', response.data.token);
    setManager(response.data.user);
    setClient(null);
    setGuest(false);
  };

  const enterAsGuest = () => {
    setGuest(true);
    setClient(null);
  };

  const logout = async () => {
    try {
      await apiClient.post('/auth/logout');
    } catch {}
    await SecureStore.deleteItemAsync('auth_token');
    setClient(null);
    setManager(null);
    setGuest(false);
  };

  const refreshProfile = async () => {
    try {
      const response = await apiClient.get<MeResponse>('/auth/me');
      applyMe(response.data.data ?? response.data);
    } catch {}
  };

  return (
    <AuthContext.Provider
      value={{
        client,
        manager,
        isAuthenticated: !!client || !!manager,
        isGuest,
        isLoading,
        registerWithEmail,
        loginWithEmail,
        loginAsManager,
        setManager,
        enterAsGuest,
        logout,
        refreshProfile,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
