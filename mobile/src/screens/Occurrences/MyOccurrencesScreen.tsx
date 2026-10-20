import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  Image,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import { apiClient } from '../../api/client';
import { Occurrence } from '../../types';

interface MyOccurrencesScreenProps {
  onSelectOccurrence: (id: number) => void;
  onNewOccurrence: () => void;
}

export const MyOccurrencesScreen: React.FC<MyOccurrencesScreenProps> = ({
  onSelectOccurrence,
  onNewOccurrence,
}) => {
  const [occurrences, setOccurrences] = useState<Occurrence[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);

  const fetchOccurrences = async () => {
    try {
      const response = await apiClient.get<{ data?: Occurrence[] } & Occurrence[]>(
        '/my-occurrences'
      );
      const list = response.data.data ?? response.data;
      if (Array.isArray(list)) {
        setOccurrences(list);
      }
    } catch {
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    fetchOccurrences();
  }, []);

  const handleRefresh = () => {
    setIsRefreshing(true);
    fetchOccurrences();
  };

  const renderOccurrenceItem = ({ item }: { item: Occurrence }) => {
    return (
      <TouchableOpacity
        style={styles.card}
        onPress={() => onSelectOccurrence(item.id)}
      >
        <View style={styles.cardHeader}>
          <Text style={styles.protocolText}>{item.protocol || 'OC-2026'}</Text>
          <View style={styles.statusBadge}>
            <Text style={styles.statusText}>{item.nameStatus || 'Em Aberto'}</Text>
          </View>
        </View>

        <View style={styles.cardBody}>
          <View style={styles.cardInfo}>
            <Text style={styles.cardTitle}>{item.title}</Text>
            <Text style={styles.cardAddress} numberOfLines={1}>
              📍 {item.address || 'Endereço registrado no mapa'}
            </Text>
            <Text style={styles.cardDate}>Registrado em: {item.created_at}</Text>
          </View>

          {item.anexo && item.anexo[0] ? (
            <Image source={{ uri: item.anexo[0] }} style={styles.cardThumb} />
          ) : (
            <View style={styles.cardThumbPlaceholder}>
              <Text style={styles.cardThumbIcon}>📷</Text>
            </View>
          )}
        </View>
      </TouchableOpacity>
    );
  };

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
        <Text style={styles.heading}>Minhas Ocorrências</Text>
        <Text style={styles.subheading}>
          Acompanhe o status e as respostas das secretarias municipais.
        </Text>
      </View>

      <FlatList
        data={occurrences}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderOccurrenceItem}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={isRefreshing} onRefresh={handleRefresh} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyIcon}>📋</Text>
            <Text style={styles.emptyTitle}>Nenhuma ocorrência registrada</Text>
            <Text style={styles.emptyText}>
              Você ainda não registrou nenhum chamado de zeladoria pública.
            </Text>
            <TouchableOpacity style={styles.createBtn} onPress={onNewOccurrence}>
              <Text style={styles.createBtnText}>Abrir Nova Ocorrência</Text>
            </TouchableOpacity>
          </View>
        }
      />
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
  cardBody: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cardInfo: {
    flex: 1,
    paddingRight: 12,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#111827',
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
  cardThumb: {
    width: 68,
    height: 68,
    borderRadius: 10,
  },
  cardThumbPlaceholder: {
    width: 68,
    height: 68,
    borderRadius: 10,
    backgroundColor: '#F3F4F6',
    justifyContent: 'center',
    alignItems: 'center',
  },
  cardThumbIcon: {
    fontSize: 24,
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
    marginBottom: 20,
    lineHeight: 20,
  },
  createBtn: {
    backgroundColor: '#1E3A8A',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 10,
  },
  createBtnText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 14,
  },
});
