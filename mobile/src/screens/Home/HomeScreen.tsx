import React, { useCallback, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { apiClient, getApiErrorMessage } from '../../api/client';
import { timeAgo } from '../../utils/timeAgo';
import { Announcement, OccurrenceStats } from '../../types';

interface HomeScreenProps {
  onOpenMap: () => void;
}

interface Issuing {
  id: number;
  name: string;
}

const STAT_CARDS: { key: keyof OccurrenceStats; label: string; color: string; background: string }[] = [
  { key: 'abertas', label: 'Abertas', color: '#B45309', background: '#FEF3C7' },
  { key: 'andamento', label: 'Em andamento', color: '#1E3A8A', background: '#DBEAFE' },
  { key: 'resolvidas', label: 'Resolvidas', color: '#065F46', background: '#D1FAE5' },
];

const LONG_BODY = 220;

// Página inicial: totais de ocorrências por etapa (filtro por órgão) + informativos dos órgãos
export const HomeScreen: React.FC<HomeScreenProps> = ({ onOpenMap }) => {
  const [issuingId, setIssuingId] = useState<number | null>(null);
  const [issuings, setIssuings] = useState<Issuing[]>([]);
  const [stats, setStats] = useState<OccurrenceStats | null>(null);
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [expanded, setExpanded] = useState<number[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const load = useCallback(async (selected: number | null) => {
    const params = selected ? { issuing_id: selected } : undefined;
    try {
      const [statsRes, announcementsRes] = await Promise.all([
        apiClient.get<{ data: OccurrenceStats; issuings: Issuing[] }>('/occurrence-stats', { params }),
        apiClient.get<{ data: Announcement[] }>('/announcements', { params }),
      ]);
      setStats(statsRes.data.data);
      setIssuings(statsRes.data.issuings);
      setAnnouncements(announcementsRes.data.data);
      setErrorMessage(null);
    } catch (err: unknown) {
      setErrorMessage(getApiErrorMessage(err) ?? 'Não foi possível carregar a página inicial.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  }, []);

  // Recarrega ao voltar para a aba (a aba fica montada)
  useFocusEffect(
    useCallback(() => {
      load(issuingId);
    }, [load, issuingId])
  );

  const selectIssuing = (id: number | null) => {
    if (id === issuingId) return;
    setIsLoading(true);
    setIssuingId(id);
  };

  const toggleExpanded = (id: number) =>
    setExpanded((current) => (current.includes(id) ? current.filter((x) => x !== id) : [...current, id]));

  const chips: { id: number | null; name: string }[] = [{ id: null, name: 'Todos os órgãos' }, ...issuings];

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.container}
        refreshControl={
          <RefreshControl
            refreshing={isRefreshing}
            onRefresh={() => {
              setIsRefreshing(true);
              load(issuingId);
            }}
          />
        }
      >
        <Text style={styles.heading}>Início</Text>
        <Text style={styles.subheading}>Acompanhe as ocorrências da cidade e os avisos dos órgãos.</Text>

        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chips}>
          {chips.map((chip) => {
            const isSelected = chip.id === issuingId;
            return (
              <TouchableOpacity
                key={chip.id ?? 'todos'}
                style={[styles.chip, isSelected && styles.chipSelected]}
                onPress={() => selectIssuing(chip.id)}
                accessibilityRole="button"
                accessibilityState={{ selected: isSelected }}
              >
                <Text style={[styles.chipText, isSelected && styles.chipTextSelected]}>{chip.name}</Text>
              </TouchableOpacity>
            );
          })}
        </ScrollView>

        {errorMessage ? (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{errorMessage}</Text>
            <TouchableOpacity onPress={() => { setIsLoading(true); load(issuingId); }}>
              <Text style={styles.retryText}>Tentar novamente</Text>
            </TouchableOpacity>
          </View>
        ) : null}

        <View style={styles.statsRow}>
          {STAT_CARDS.map((card) => (
            <View key={card.key} style={[styles.statCard, { backgroundColor: card.background }]}>
              {isLoading || !stats ? (
                <ActivityIndicator color={card.color} />
              ) : (
                <Text style={[styles.statValue, { color: card.color }]}>{stats[card.key]}</Text>
              )}
              <Text style={[styles.statLabel, { color: card.color }]}>{card.label}</Text>
            </View>
          ))}
        </View>

        <TouchableOpacity style={styles.mapButton} onPress={onOpenMap}>
          <Text style={styles.mapButtonText}>🗺️  Ver mapa</Text>
        </TouchableOpacity>

        <Text style={styles.sectionTitle}>Informativos</Text>
        {!isLoading && announcements.length === 0 ? (
          <Text style={styles.emptyText}>Nenhum informativo publicado no momento.</Text>
        ) : null}
        {announcements.map((item) => {
          const isLong = item.body.length > LONG_BODY;
          const isOpen = expanded.includes(item.id);
          return (
            <View key={item.id} style={styles.card}>
              <Text style={styles.cardMeta}>
                {item.issuing?.name ?? 'Prefeitura'}
                {item.published_at ? ` · ${timeAgo(item.published_at)}` : ''}
              </Text>
              <Text style={styles.cardTitle}>{item.title}</Text>
              <Text style={styles.cardBody} numberOfLines={isLong && !isOpen ? 4 : undefined}>
                {item.body}
              </Text>
              {isLong ? (
                <TouchableOpacity onPress={() => toggleExpanded(item.id)}>
                  <Text style={styles.readMore}>{isOpen ? 'Mostrar menos' : 'Ler mais'}</Text>
                </TouchableOpacity>
              ) : null}
            </View>
          );
        })}
      </ScrollView>
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
    paddingBottom: 32,
  },
  heading: {
    fontSize: 26,
    fontWeight: '800',
    color: '#111827',
  },
  subheading: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 4,
  },
  chips: {
    gap: 8,
    paddingVertical: 16,
  },
  chip: {
    paddingHorizontal: 14,
    height: 36,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
  },
  chipSelected: {
    backgroundColor: '#1E3A8A',
    borderColor: '#1E3A8A',
  },
  chipText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#374151',
  },
  chipTextSelected: {
    color: '#FFFFFF',
  },
  errorBox: {
    backgroundColor: '#FEF2F2',
    borderRadius: 10,
    padding: 12,
    marginBottom: 12,
  },
  errorText: {
    color: '#B91C1C',
    fontSize: 13,
  },
  retryText: {
    color: '#1E3A8A',
    fontWeight: '700',
    marginTop: 6,
  },
  statsRow: {
    flexDirection: 'row',
    gap: 10,
  },
  statCard: {
    flex: 1,
    minHeight: 92,
    borderRadius: 14,
    padding: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  statValue: {
    fontSize: 28,
    fontWeight: '800',
  },
  statLabel: {
    marginTop: 4,
    fontSize: 12,
    fontWeight: '700',
    textAlign: 'center',
  },
  mapButton: {
    marginTop: 14,
    height: 48,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: '#1E3A8A',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
  },
  mapButtonText: {
    color: '#1E3A8A',
    fontSize: 15,
    fontWeight: '700',
  },
  sectionTitle: {
    marginTop: 28,
    marginBottom: 12,
    fontSize: 18,
    fontWeight: '800',
    color: '#111827',
  },
  emptyText: {
    fontSize: 14,
    color: '#6B7280',
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    marginBottom: 12,
  },
  cardMeta: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1E3A8A',
    marginBottom: 6,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 6,
  },
  cardBody: {
    fontSize: 14,
    color: '#4B5563',
    lineHeight: 20,
  },
  readMore: {
    marginTop: 8,
    color: '#1E3A8A',
    fontWeight: '700',
  },
});
