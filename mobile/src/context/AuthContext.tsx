import React, { createContext, useContext, useState, useEffect } from 'react';
import * as SecureStore from 'expo-secure-store';
import { apiClient } from '../api/client';
import { Client, Manager } from '../types';

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
  loginWithPhone: (name: string, phone: string, email?: string) => Promise<void>;
  registerWithEmail: (data: RegisterPayload) => Promise<void>;
  socialAuth: (provider: 'google' | 'apple', providerId: string, email?: string, name?: string) => Promise<void>;
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
        }
      } catch {
        await SecureStore.deleteItemAsync('auth_token');
      } finally {
        setIsLoading(false);
      }
    })();
  }, []);

  const loginWithPhone = async (name: string, phone: string, email?: string) => {
    const response = await apiClient.post<{ token: string; client: Client }>('/auth/citizen', {
      name,
      phone,
      email,
    });
    const { token, client: clientData } = response.data;
    await SecureStore.setItemAsync('auth_token', token);
    setClient(clientData);
    setIsGuest(false);
  };

  const registerWithEmail = async (data: RegisterPayload) => {
    const response = await apiClient.post<{ token: string; client: Client }>('/auth/register', {
      ...data,
      terms_accepted: data.terms_accepted ? 1 : 0,
    });
    const { token, client: clientData } = response.data;
    await SecureStore.setItemAsync('auth_token', token);
    setClient(clientData);
    setIsGuest(false);
  };

  const socialAuth = async (
    provider: 'google' | 'apple',
    providerId: string,
    email?: string,
    name?: string
  ) => {
    const response = await apiClient.post<{ token: string; client: Client }>('/auth/social', {
      provider,
      provider_id: providerId,
      email,
      name,
    });
    const { token, client: clientData } = response.data;
    await SecureStore.setItemAsync('auth_token', token);
    setClient(clientData);
    setIsGuest(false);
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
    setIsGuest(false);
  };

  const enterAsGuest = () => {
    setIsGuest(true);
    setClient(null);
  };

  const logout = async () => {
    try {
      await apiClient.post('/auth/logout');
    } catch {}
    await SecureStore.deleteItemAsync('auth_token');
    setClient(null);
    setManager(null);
    setIsGuest(false);
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
        loginWithPhone,
        registerWithEmail,
        socialAuth,
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
