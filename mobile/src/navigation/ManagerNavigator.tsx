import React, { useState, useEffect, useCallback } from 'react';
import { Text, StyleSheet } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { apiClient } from '../api/client';
import { ManagerOccurrencesScreen } from '../screens/Manager/ManagerOccurrencesScreen';
import { ManagerOccurrenceDetailScreen } from '../screens/Manager/ManagerOccurrenceDetailScreen';
import { NotificationsScreen } from '../screens/Manager/NotificationsScreen';
import { ManagerAccountScreen } from '../screens/Manager/ManagerAccountScreen';

const Tab = createBottomTabNavigator();

// Sem push: o badge de não lidas é atualizado por polling enquanto o app está aberto
const UNREAD_POLL_MS = 60000;

export const ManagerNavigator: React.FC = () => {
  const [selectedOccurrenceId, setSelectedOccurrenceId] = useState<number | null>(null);
  const [unreadCount, setUnreadCount] = useState(0);

  useEffect(() => {
    const fetchUnread = () =>
      apiClient
        .get<{ meta: { unread_count: number } }>('/manager/notifications')
        .then((response) => setUnreadCount(response.data.meta.unread_count))
        .catch(() => {});

    fetchUnread();
    const interval = setInterval(fetchUnread, UNREAD_POLL_MS);
    return () => clearInterval(interval);
  }, []);

  const handleUnreadCountChange = useCallback((count: number) => setUnreadCount(count), []);

  return (
    <Tab.Navigator
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: '#1E3A8A',
        tabBarInactiveTintColor: '#9CA3AF',
        tabBarStyle: styles.tabBar,
        tabBarLabelStyle: styles.tabBarLabel,
      }}
    >
      <Tab.Screen
        name="GestorOcorrencias"
        options={{
          tabBarLabel: 'Ocorrências',
          tabBarIcon: ({ color }) => <Text style={{ fontSize: 20, color }}>📋</Text>,
        }}
      >
        {() =>
          selectedOccurrenceId !== null ? (
            <ManagerOccurrenceDetailScreen
              occurrenceId={selectedOccurrenceId}
              onBack={() => setSelectedOccurrenceId(null)}
            />
          ) : (
            <ManagerOccurrencesScreen onSelectOccurrence={setSelectedOccurrenceId} />
          )
        }
      </Tab.Screen>

      <Tab.Screen
        name="GestorNotificacoes"
        options={{
          tabBarLabel: 'Notificações',
          tabBarBadge: unreadCount > 0 ? unreadCount : undefined,
          tabBarIcon: ({ color }) => <Text style={{ fontSize: 20, color }}>🔔</Text>,
        }}
      >
        {({ navigation }) => (
          <NotificationsScreen
            onUnreadCountChange={handleUnreadCountChange}
            onOpenSettings={() => navigation.navigate('GestorConta')}
            onOpenOccurrence={(occurrenceId) => {
              setSelectedOccurrenceId(occurrenceId);
              navigation.navigate('GestorOcorrencias');
            }}
          />
        )}
      </Tab.Screen>

      <Tab.Screen
        name="GestorConta"
        component={ManagerAccountScreen}
        options={{
          tabBarLabel: 'Conta',
          tabBarIcon: ({ color }) => <Text style={{ fontSize: 20, color }}>👤</Text>,
        }}
      />
    </Tab.Navigator>
  );
};

const styles = StyleSheet.create({
  tabBar: {
    height: 64,
    paddingBottom: 8,
    paddingTop: 6,
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    backgroundColor: '#FFFFFF',
  },
  tabBarLabel: {
    fontSize: 11,
    fontWeight: '600',
  },
});
