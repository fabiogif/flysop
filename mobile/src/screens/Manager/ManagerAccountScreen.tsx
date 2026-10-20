import React, { useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, SafeAreaView, Switch } from 'react-native';
import { useAuth } from '../../context/AuthContext';
import { apiClient } from '../../api/client';

// Conta do gestor + preferências de notificação (destino do atalho ⚙️ do feed)
export const ManagerAccountScreen: React.FC = () => {
  const { manager, setManager, logout } = useAuth();
  const [isSaving, setIsSaving] = useState(false);
  const [message, setMessage] = useState<string | null>(null);

  if (!manager) return null;

  const toggleNotifications = async (value: boolean) => {
    setIsSaving(true);
    setMessage(null);
    setManager({ ...manager, notify_new_occurrences: value });
    try {
      await apiClient.put('/manager/notification-settings', { notify_new_occurrences: value });
    } catch {
      setManager({ ...manager, notify_new_occurrences: !value });
      setMessage('Não foi possível salvar a preferência.');
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.container}>
        <Text style={styles.heading}>Minha conta</Text>

        <View style={styles.card}>
          <Text style={styles.name}>{manager.name}</Text>
          <Text style={styles.meta}>{manager.email}</Text>
          <Text style={styles.issuing}>🏛️ {manager.issuing?.name}</Text>
        </View>

        <Text style={styles.sectionTitle}>Notificações</Text>
        <View style={styles.card}>
          <View style={styles.settingRow}>
            <View style={styles.settingText}>
              <Text style={styles.settingLabel}>Novas ocorrências do órgão</Text>
              <Text style={styles.meta}>Receber aviso quando um cidadão registrar uma ocorrência.</Text>
            </View>
            <Switch
              value={manager.notify_new_occurrences}
              onValueChange={toggleNotifications}
              disabled={isSaving}
              trackColor={{ true: '#1E3A8A', false: '#D1D5DB' }}
              accessibilityLabel="Receber aviso de novas ocorrências"
            />
          </View>
          {message ? <Text style={styles.error}>{message}</Text> : null}
        </View>

        <TouchableOpacity style={styles.logoutButton} onPress={logout}>
          <Text style={styles.logoutText}>Sair</Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  container: {
    padding: 20,
  },
  heading: {
    fontSize: 22,
    fontWeight: '800',
    color: '#111827',
    marginBottom: 16,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    marginBottom: 20,
  },
  name: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
  },
  meta: {
    fontSize: 13,
    color: '#6B7280',
    marginTop: 2,
  },
  issuing: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1E3A8A',
    marginTop: 10,
  },
  sectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#6B7280',
    textTransform: 'uppercase',
    marginBottom: 8,
  },
  settingRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  settingText: {
    flex: 1,
    paddingRight: 12,
  },
  settingLabel: {
    fontSize: 15,
    fontWeight: '600',
    color: '#111827',
  },
  error: {
    color: '#B91C1C',
    fontSize: 13,
    marginTop: 8,
  },
  logoutButton: {
    height: 48,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: '#DC2626',
    justifyContent: 'center',
    alignItems: 'center',
  },
  logoutText: {
    color: '#DC2626',
    fontWeight: '700',
    fontSize: 15,
  },
});
