import React, { useState, useCallback, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  FlatList,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import { apiClient, getApiErrorMessage } from '../../api/client';
import { getGuestProtocols, saveGuestProtocol, GuestProtocol } from '../../services/guestProtocols';
import { Occurrence } from '../../types';

// Sem cadastro: acompanha pelas ocorrências registradas neste aparelho ou digitando protocolo +
// código de acompanhamento (a API só mostra a ocorrência para quem tem os dois)
export const GuestOccurrencesScreen: React.FC = () => {
  const [occurrences, setOccurrences] = useState<Occurrence[]>([]);
  const [protocol, setProtocol] = useState('');
  const [code, setCode] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [isSearching, setIsSearching] = useState(false);
  const [message, setMessage] = useState<string | null>(null);

  const fetchByProtocol = ({ protocol: value, code: trackingCode }: GuestProtocol) =>
    apiClient
      .get<{ data: Occurrence }>(`/occurrences/protocol/${encodeURIComponent(value)}`, {
        headers: trackingCode ? { 'X-Tracking-Code': trackingCode } : undefined,
      })
      .then((response) => response.data.data);

  const loadSaved = useCallback(async () => {
    const saved = await getGuestProtocols();
    const results = await Promise.allSettled(saved.map(fetchByProtocol));
    setOccurrences(
      results.flatMap((result) => (result.status === 'fulfilled' ? [result.value] : []))
    );
    setIsLoading(false);
  }, []);

  useEffect(() => {
    loadSaved();
  }, [loadSaved]);

  const handleSearch = async () => {
    const value = protocol.trim().toUpperCase();
    const trackingCode = code.trim().toUpperCase();
    if (!value || !trackingCode) {
      setMessage('Informe o protocolo e o código de acompanhamento.');
      return;
    }
    setIsSearching(true);
    setMessage(null);
    try {
      const found = await fetchByProtocol({ protocol: value, code: trackingCode });
      await saveGuestProtocol(value, trackingCode);
      setOccurrences((current) => [found, ...current.filter((o) => o.id !== found.id)]);
      setProtocol('');
      setCode('');
    } catch (err: unknown) {
      setMessage(getApiErrorMessage(err) ?? 'Não foi possível consultar o protocolo.');
    } finally {
      setIsSearching(false);
    }
  };

  const renderItem = ({ item }: { item: Occurrence }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <Text style={styles.protocolText}>{item.protocol}</Text>
        <View style={styles.statusBadge}>
          <Text style={styles.statusText}>{item.nameStatus}</Text>
        </View>
      </View>
      <Text style={styles.cardTitle}>{item.title}</Text>
      <Text style={styles.cardMeta}>
        {item.nameType} · registrada em {item.created_at}
      </Text>
    </View>
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <Text style={styles.heading}>Acompanhar ocorrência</Text>
        <Text style={styles.subheading}>
          Você está sem cadastro: acompanhe pelo protocolo e código de acompanhamento.
        </Text>
        <TextInput
          style={[styles.input, styles.inputStacked]}
          value={protocol}
          onChangeText={setProtocol}
          placeholder="Protocolo (ex: OC-2026-00008)"
          placeholderTextColor="#9CA3AF"
          autoCapitalize="characters"
          accessibilityLabel="Número de protocolo"
        />
        <View style={styles.searchRow}>
          <TextInput
            style={styles.input}
            value={code}
            onChangeText={setCode}
            placeholder="Código (ex: K7QM-3XPR)"
            placeholderTextColor="#9CA3AF"
            autoCapitalize="characters"
            autoCorrect={false}
            onSubmitEditing={handleSearch}
            accessibilityLabel="Código de acompanhamento"
          />
          <TouchableOpacity style={styles.searchBtn} onPress={handleSearch} disabled={isSearching}>
            {isSearching ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <Text style={styles.searchBtnText}>Consultar</Text>
            )}
          </TouchableOpacity>
        </View>
        {message ? <Text style={styles.error}>{message}</Text> : null}
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
          refreshControl={<RefreshControl refreshing={false} onRefresh={loadSaved} />}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>🔎</Text>
              <Text style={styles.emptyTitle}>Nenhuma ocorrência neste aparelho</Text>
              <Text style={styles.emptyText}>
                As ocorrências que você registrar aparecem aqui. De outro aparelho, consulte com o
                protocolo e o código de acompanhamento acima.
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
    paddingBottom: 14,
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
    marginBottom: 12,
  },
  searchRow: {
    flexDirection: 'row',
    gap: 8,
  },
  input: {
    flex: 1,
    height: 48,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 10,
    paddingHorizontal: 14,
    fontSize: 15,
    color: '#111827',
    backgroundColor: '#F9FAFB',
  },
  // Fora da linha do botão: sem flex (senão estica na vertical dentro do cabeçalho)
  inputStacked: {
    flex: 0,
    marginBottom: 8,
  },
  searchBtn: {
    height: 48,
    paddingHorizontal: 16,
    borderRadius: 10,
    backgroundColor: '#1E3A8A',
    justifyContent: 'center',
    alignItems: 'center',
  },
  searchBtnText: {
    color: '#FFFFFF',
    fontWeight: '700',
  },
  error: {
    color: '#B91C1C',
    fontSize: 13,
    marginTop: 8,
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
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
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
  },
  cardMeta: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 4,
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
