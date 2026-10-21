import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import { apiClient, getApiErrorMessage } from '../../api/client';
import { Survey } from '../../types';

interface SurveyListScreenProps {
  onSelectSurvey: (publicToken: string) => void;
}

export const SurveyListScreen: React.FC<SurveyListScreenProps> = ({ onSelectSurvey }) => {
  const [surveys, setSurveys] = useState<Survey[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const fetchSurveys = async () => {
    try {
      const response = await apiClient.get<{ data?: Survey[] } & Survey[]>('/surveys');
      const list = response.data.data ?? response.data;
      if (Array.isArray(list)) {
        setSurveys(list);
      }
      setErrorMessage(null);
    } catch (err: unknown) {
      // Antes o erro virava "Nenhuma pesquisa no momento", mesmo com pesquisas ativas
      setErrorMessage(getApiErrorMessage(err) ?? 'Não foi possível carregar as pesquisas.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    fetchSurveys();
  }, []);

  const handleRefresh = () => {
    setIsRefreshing(true);
    fetchSurveys();
  };

  const renderSurveyItem = ({ item }: { item: Survey }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <Text style={styles.badgeActive}>Ativa</Text>
      </View>
      <Text style={styles.title}>{item.title}</Text>
      {item.description ? (
        <Text style={styles.description} numberOfLines={2}>
          {item.description}
        </Text>
      ) : null}

      <TouchableOpacity
        style={styles.answerBtn}
        onPress={() => onSelectSurvey(item.public_token)}
      >
        <Text style={styles.answerBtnText}>Responder Pesquisa</Text>
      </TouchableOpacity>
    </View>
  );

  if (isLoading) {
    return (
      <SafeAreaView style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#1E3A8A" />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <Text style={styles.heading}>Pesquisas de Opinião</Text>
        <Text style={styles.subheading}>
          Ajude a prefeitura a priorizar obras e serviços respondendo enquetes.
        </Text>
      </View>

      <FlatList
        data={surveys}
        keyExtractor={(item) => item.public_token}
        renderItem={renderSurveyItem}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={isRefreshing} onRefresh={handleRefresh} />
        }
        ListEmptyComponent={
          errorMessage ? (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>⚠️</Text>
              <Text style={styles.emptyTitle}>Não foi possível carregar</Text>
              <Text style={styles.emptyText}>{errorMessage}</Text>
              <TouchableOpacity style={styles.retryBtn} onPress={handleRefresh} accessibilityRole="button">
                <Text style={styles.answerBtnText}>Tentar novamente</Text>
              </TouchableOpacity>
            </View>
          ) : (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>📊</Text>
            <Text style={styles.emptyTitle}>Nenhuma pesquisa no momento</Text>
            <Text style={styles.emptyText}>
              Não há pesquisas de opinião ativas no seu município hoje.
            </Text>
          </View>
          )
        }
      />
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  retryBtn: {
    marginTop: 16,
    height: 44,
    paddingHorizontal: 20,
    backgroundColor: '#1E3A8A',
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  safeArea: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  heading: {
    fontSize: 22,
    fontWeight: '800',
    color: '#111827',
  },
  subheading: {
    fontSize: 13,
    color: '#6B7280',
    marginTop: 4,
  },
  listContent: {
    padding: 16,
    gap: 12,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    shadowColor: '#000',
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 2,
  },
  cardHeader: {
    flexDirection: 'row',
    marginBottom: 8,
  },
  badgeActive: {
    fontSize: 11,
    fontWeight: '700',
    color: '#065F46',
    backgroundColor: '#D1FAE5',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  title: {
    fontSize: 16,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 6,
  },
  description: {
    fontSize: 13,
    color: '#4B5563',
    lineHeight: 18,
    marginBottom: 14,
  },
  answerBtn: {
    height: 44,
    backgroundColor: '#1E3A8A',
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  answerBtnText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 14,
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
  },
  emptyContainer: {
    paddingVertical: 60,
    alignItems: 'center',
    paddingHorizontal: 24,
  },
  emptyIcon: {
    fontSize: 54,
    marginBottom: 14,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 6,
  },
  emptyText: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
  },
});
