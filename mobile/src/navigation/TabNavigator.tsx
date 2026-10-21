import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import * as Haptics from 'expo-haptics';
import { MapScreen } from '../screens/Occurrences/MapScreen';
import { HomeScreen } from '../screens/Home/HomeScreen';
import { MyOccurrencesScreen } from '../screens/Occurrences/MyOccurrencesScreen';
import { NewOccurrenceScreen } from '../screens/Occurrences/NewOccurrenceScreen';
import { OccurrenceDetailScreen } from '../screens/Occurrences/OccurrenceDetailScreen';
import { SurveyListScreen } from '../screens/Surveys/SurveyListScreen';
import { SurveyDetailScreen } from '../screens/Surveys/SurveyDetailScreen';
import { EditProfileScreen } from '../screens/Profile/EditProfileScreen';
import { GuestOccurrencesScreen } from '../screens/Occurrences/GuestOccurrencesScreen';
import { useAuth } from '../context/AuthContext';
import { Coordinates } from '../components/Map/osmStyle';
import { apiClient } from '../api/client';
import { getAnsweredSurveys } from '../services/answeredSurveys';
import { Survey } from '../types';

const Tab = createBottomTabNavigator();

// Sub-fluxo do Mapa com detalhe de ocorrência
const MapFlow: React.FC<{ onNavigateToNew: (point?: Coordinates) => void }> = ({ onNavigateToNew }) => {
  const [selectedDetailId, setSelectedDetailId] = useState<number | null>(null);

  if (selectedDetailId !== null) {
    return (
      <OccurrenceDetailScreen
        occurrenceId={selectedDetailId}
        onBack={() => setSelectedDetailId(null)}
      />
    );
  }

  return (
    <MapScreen
      onSelectOccurrenceDetail={(id) => setSelectedDetailId(id)}
      onOpenNewOccurrence={onNavigateToNew}
    />
  );
};

// Aba Início: página inicial (dashboard + informativos); o mapa abre a partir dela
const HomeFlow: React.FC<{ onNavigateToNew: (point?: Coordinates) => void }> = ({ onNavigateToNew }) => {
  const [isMapOpen, setIsMapOpen] = useState(false);

  if (isMapOpen) {
    return (
      <View style={styles.flowContainer}>
        <TouchableOpacity style={styles.backBar} onPress={() => setIsMapOpen(false)} accessibilityRole="button">
          <Text style={styles.backBarText}>← Voltar ao início</Text>
        </TouchableOpacity>
        <MapFlow onNavigateToNew={onNavigateToNew} />
      </View>
    );
  }

  return <HomeScreen onOpenMap={() => setIsMapOpen(true)} />;
};

// Sub-fluxo de Minhas Ocorrências com detalhe
const OccurrencesFlow: React.FC<{ onNavigateToNew: () => void }> = ({ onNavigateToNew }) => {
  const [selectedDetailId, setSelectedDetailId] = useState<number | null>(null);
  const { isGuest } = useAuth();

  // Sem cadastro não há "minhas ocorrências" na conta: acompanha por protocolo
  if (isGuest) {
    return <GuestOccurrencesScreen />;
  }

  if (selectedDetailId !== null) {
    return (
      <OccurrenceDetailScreen
        occurrenceId={selectedDetailId}
        onBack={() => setSelectedDetailId(null)}
      />
    );
  }

  return (
    <MyOccurrencesScreen
      onSelectOccurrence={(id) => setSelectedDetailId(id)}
      onNewOccurrence={onNavigateToNew}
    />
  );
};

// Sub-fluxo de Pesquisas Públicas com detalhe de questionário
const SurveysFlow: React.FC<{ onSurveyClosed: () => void }> = ({ onSurveyClosed }) => {
  const [activeSurveyToken, setActiveSurveyToken] = useState<string | null>(null);

  if (activeSurveyToken !== null) {
    return (
      <SurveyDetailScreen
        token={activeSurveyToken}
        onFinish={() => {
          setActiveSurveyToken(null);
          onSurveyClosed();
        }}
      />
    );
  }

  return (
    <SurveyListScreen
      onSelectSurvey={(token) => setActiveSurveyToken(token)}
    />
  );
};

export const TabNavigator: React.FC = () => {
  const [pendingSurveys, setPendingSurveys] = useState(0);

  // Badge = pesquisas ativas ainda não respondidas neste aparelho
  const refreshSurveyBadge = useCallback(async () => {
    try {
      const [response, answered] = await Promise.all([
        apiClient.get<{ data?: Survey[] } & Survey[]>('/surveys'),
        getAnsweredSurveys(),
      ]);
      const list = response.data.data ?? response.data;
      if (Array.isArray(list)) {
        setPendingSurveys(list.filter((s) => !answered.includes(s.public_token)).length);
      }
    } catch {}
  }, []);

  useEffect(() => {
    refreshSurveyBadge();
  }, [refreshSurveyBadge]);

  return (
    <Tab.Navigator
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: '#1E3A8A',
        tabBarInactiveTintColor: '#9CA3AF',
        tabBarStyle: styles.tabBar,
        tabBarLabelStyle: styles.tabBarLabel,
        tabBarItemStyle: styles.tabBarItem,
      }}
      screenListeners={{
        tabPress: () => {
          Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
        },
        // Recalcula a cada troca de aba: com a API acordando (máquina parada no Fly) a 1ª
        // consulta pode estourar o timeout e o badge ficaria sumido até a próxima resposta
        focus: () => {
          refreshSurveyBadge();
        },
      }}
    >
      <Tab.Screen
        name="InicioTab"
        options={{
          tabBarLabel: 'Início',
          tabBarIcon: ({ color, focused }) => (
            <Text style={{ fontSize: 20, color }}>{focused ? '🏠' : '🏡'}</Text>
          ),
        }}
      >
        {({ navigation }) => (
          <HomeFlow onNavigateToNew={(point) => navigation.navigate('NovaTab', { point })} />
        )}
      </Tab.Screen>

      <Tab.Screen
        name="OcorrenciasTab"
        options={{
          tabBarLabel: 'Ocorrências',
          tabBarIcon: ({ color, focused }) => (
            <Text style={{ fontSize: 20, color }}>{focused ? '📋' : '📄'}</Text>
          ),
        }}
      >
        {({ navigation }) => (
          <OccurrencesFlow onNavigateToNew={() => navigation.navigate('NovaTab')} />
        )}
      </Tab.Screen>

      <Tab.Screen
        name="NovaTab"
        options={{
          // Só o "+": sem rótulo; o nome fica para leitor de tela
          tabBarLabel: () => null,
          tabBarAccessibilityLabel: 'Nova ocorrência',
          tabBarIcon: () => (
            <View style={styles.centerActionButton}>
              <Text style={styles.centerActionText}>＋</Text>
            </View>
          ),
        }}
      >
        {({ navigation, route }) => (
          <NewOccurrenceScreen
            initialPoint={(route.params as { point?: Coordinates } | undefined)?.point}
            onNavigateHome={() => {
              navigation.setParams({ point: undefined });
              navigation.navigate('InicioTab');
            }}
          />
        )}
      </Tab.Screen>

      <Tab.Screen
        name="PesquisasTab"
        options={{
          tabBarLabel: 'Pesquisas',
          tabBarBadge: pendingSurveys > 0 ? pendingSurveys : undefined,
          tabBarIcon: ({ color, focused }) => (
            <Text style={{ fontSize: 20, color }}>{focused ? '📊' : '📉'}</Text>
          ),
        }}
      >
        {() => <SurveysFlow onSurveyClosed={refreshSurveyBadge} />}
      </Tab.Screen>

      <Tab.Screen
        name="PerfilTab"
        component={EditProfileScreen}
        options={{
          tabBarLabel: 'Perfil',
          tabBarIcon: ({ color, focused }) => (
            <Text style={{ fontSize: 20, color }}>{focused ? '👤' : '👥'}</Text>
          ),
        }}
      />
    </Tab.Navigator>
  );
};

const styles = StyleSheet.create({
  flowContainer: {
    flex: 1,
  },
  backBar: {
    height: 48,
    paddingHorizontal: 16,
    justifyContent: 'center',
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  backBarText: {
    color: '#1E3A8A',
    fontSize: 15,
    fontWeight: '700',
  },
  tabBar: {
    height: 64,
    paddingBottom: 8,
    paddingTop: 6,
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    backgroundColor: '#FFFFFF',
  },
  tabBarItem: {
    minHeight: 48,
    minWidth: 48,
  },
  tabBarLabel: {
    fontSize: 11,
    fontWeight: '600',
  },
  centerActionButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#1E3A8A',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 6,
    shadowColor: '#1E3A8A',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 4,
  },
  centerActionText: {
    color: '#FFFFFF',
    fontSize: 24,
    fontWeight: '700',
    lineHeight: 28,
  },
});
