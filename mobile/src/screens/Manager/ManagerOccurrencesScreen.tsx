import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  RefreshControl,
  ActivityIndicator,
  ScrollView,
} from 'react-native';
import { apiClient } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { Occurrence, StatusOccurrence } from '../../types';

interface ManagerOccurrencesScreenProps {
  onSelectOccurrence: (id: number) => void;
}

type Paginated<T> = { data: T[]; meta?: { current_page: number; last_page: number } };

// Ocorrências do órgão do gestor, mais recentes primeiro, com filtro por status
export const ManagerOccurrencesScreen: React.FC<ManagerOccurrencesScreenProps> = ({
  onSelectOccurrence,
}) => {
  const { manager } = useAuth();
  const [occurrences, setOccurrences] = useState<Occurrence[]>([]);
  const [statuses, setStatuses] = useState<StatusOccurrence[]>([]);
  const [statusId, setStatusId] = useState<number | null>(null);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);

  const fetchPage = useCallback(
    async (nextPage: number) => {
      try {
        const response = await apiClient.get<Paginated<Occurrence>>('/manager/occurrences', {
          params: { page: nextPage, status_occurrences_id: statusId ?? undefined },
        });
        setOccurrences((current) =>
          nextPage === 1 ? response.data.data : [...current, ...response.data.data]
        );
        setPage(response.data.meta?.current_page ?? nextPage);
        setLastPage(response.data.meta?.last_page ?? nextPage);
      } catch (error) {
        console.warn('ManagerOccurrences: falha ao carregar', error);
      } finally {
        setIsLoading(false);
        setIsRefreshing(false);
      }
    },
    [statusId]
  );

  useEffect(() => {
    apiClient
      .get<{ data: StatusOccurrence[] }>('/manager/status-occurrences')
      .then((response) => setStatuses(response.data.data))
      .catch(() => {});
  }, []);

  useEffect(() => {
    setIsLoading(true);
    fetchPage(1);
  }, [fetchPage]);

  const handleRefresh = () => {
    setIsRefreshing(true);
    fetchPage(1);
  };

  const handleEndReached = () => {
    if (page < lastPage) fetchPage(page + 1);
  };

  const renderItem = ({ item }: { item: Occurrence }) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() => onSelectOccurrence(item.id)}
      accessibilityLabel={`${item.protocol} ${item.title}`}
    >
      <View style={styles.cardHeader}>
        <Text style={styles.protocolText}>{item.protocol}</Text>
        <View style={styles.statusBadge}>
          <Text style={styles.statusText}>{item.nameStatus}</Text>
        </View>
      </View>
      <Text style={styles.cardTitle}>{item.title}</Text>
      {item.nameType ? <Text style={styles.cardType}>{item.nameType}</Text> : null}
      <Text style={styles.cardAddress} numberOfLines={1}>
        📍 {item.address || 'Endereço não informado'}
      </Text>
      <Text style={styles.cardDate}>Registrada em {item.created_at}</Text>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <Text style={styles.heading}>Ocorrências do órgão</Text>
        <Text style={styles.subheading}>{manager?.issuing?.name}</Text>
      </View>

      <View>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.filters}>
          {[{ id: null, name: 'Todas' }, ...statuses].map((status) => {
            const isActive = statusId === status.id;
            return (
              <TouchableOpacity
                key={status.id ?? 'all'}
                style={[styles.filterChip, isActive && styles.filterChipActive]}
                onPress={() => setStatusId(status.id)}
              >
                <Text style={[styles.filterChipText, isActive && styles.filterChipTextActive]}>
                  {status.name}
                </Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>
      </View>

      {isLoading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color="#1E3A8A" />
        </View>
      ) : (
        <FlatList
          data={occurrences}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderItem}
          contentContainerStyle={styles.listContent}
          onEndReached={handleEndReached}
          onEndReachedThreshold={0.4}
          refreshControl={<RefreshControl refreshing={isRefreshing} onRefresh={handleRefresh} />}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>📭</Text>
              <Text style={styles.emptyTitle}>Nenhuma ocorrência por aqui</Text>
              <Text style={styles.emptyText}>
                Quando um cidadão registrar uma ocorrência para o seu órgão, ela aparece nesta lista.
              </Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
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
  filters: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    gap: 8,
  },
  filterChip: {
    paddingHorizontal: 14,
    minHeight: 40,
    justifyContent: 'center',
    borderRadius: 20,
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#D1D5DB',
  },
  filterChipActive: {
    backgroundColor: '#1E3A8A',
    borderColor: '#1E3A8A',
  },
  filterChipText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#374151',
  },
  filterChipTextActive: {
    color: '#FFFFFF',
  },
  listContent: {
    padding: 16,
    paddingTop: 4,
    gap: 12,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  protocolText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1E3A8A',
    backgroundColor: '#EFF6FF',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  statusBadge: {
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#065F46',
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 2,
  },
  cardType: {
    fontSize: 13,
    color: '#1E3A8A',
    fontWeight: '600',
    marginBottom: 4,
  },
  cardAddress: {
    fontSize: 13,
    color: '#4B5563',
    marginBottom: 6,
  },
  cardDate: {
    fontSize: 12,
    color: '#9CA3AF',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
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
    lineHeight: 20,
  },
});
