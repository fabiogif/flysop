import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
} from 'react-native';
import { apiClient, getApiErrorMessage } from '../../api/client';
import { Occurrence, OccurrenceHistoryEntry, StatusOccurrence } from '../../types';
import { timeAgo } from '../../utils/timeAgo';

interface ManagerOccurrenceDetailScreenProps {
  occurrenceId: number;
  onBack: () => void;
}

type Requester = { name: string | null; email: string | null; phone: string | null };

// Detalhe para análise do gestor: dados do chamado, histórico e mudança de status
export const ManagerOccurrenceDetailScreen: React.FC<ManagerOccurrenceDetailScreenProps> = ({
  occurrenceId,
  onBack,
}) => {
  const [occurrence, setOccurrence] = useState<Occurrence | null>(null);
  const [requester, setRequester] = useState<Requester | null>(null);
  const [history, setHistory] = useState<OccurrenceHistoryEntry[]>([]);
  const [statuses, setStatuses] = useState<StatusOccurrence[]>([]);
  const [selectedStatusId, setSelectedStatusId] = useState<number | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [message, setMessage] = useState<{ text: string; isError: boolean } | null>(null);

  const load = async () => {
    try {
      const [detail, statusList] = await Promise.all([
        apiClient.get<{ data: Occurrence; requester: Requester; history: OccurrenceHistoryEntry[] }>(
          `/manager/occurrences/${occurrenceId}`
        ),
        apiClient.get<{ data: StatusOccurrence[] }>('/manager/status-occurrences'),
      ]);
      setOccurrence(detail.data.data);
      setRequester(detail.data.requester);
      setHistory(detail.data.history);
      setStatuses(statusList.data.data);
      setSelectedStatusId(detail.data.data.status_occurrences_id);
    } catch (err: unknown) {
      setMessage({ text: getApiErrorMessage(err) ?? 'Não foi possível carregar a ocorrência.', isError: true });
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, [occurrenceId]);

  const handleChangeStatus = async () => {
    if (!occurrence || selectedStatusId === null) return;
    setIsSaving(true);
    setMessage(null);
    try {
      await apiClient.patch(`/manager/occurrences/${occurrence.id}/status`, {
        status_occurrences_id: selectedStatusId,
      });
      await load();
      setMessage({ text: 'Status atualizado.', isError: false });
    } catch (err: unknown) {
      setMessage({ text: getApiErrorMessage(err) ?? 'Não foi possível mudar o status.', isError: true });
    } finally {
      setIsSaving(false);
    }
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
      <View style={styles.topBar}>
        <TouchableOpacity onPress={onBack} style={styles.backBtn} accessibilityRole="button">
          <Text style={styles.backBtnText}>← Voltar</Text>
        </TouchableOpacity>
        <Text style={styles.topBarProtocol}>{occurrence?.protocol}</Text>
      </View>

      <ScrollView contentContainerStyle={styles.container}>
        {message && (
          <View style={[styles.messageBox, message.isError && styles.messageBoxError]}>
            <Text style={[styles.messageText, message.isError && styles.messageTextError]}>
              {message.text}
            </Text>
          </View>
        )}

        {occurrence && (
          <>
            <View style={styles.card}>
              <View style={styles.badgeRow}>
                {occurrence.nameType ? <Text style={styles.typeBadge}>{occurrence.nameType}</Text> : null}
                <Text style={styles.statusBadge}>{occurrence.nameStatus}</Text>
              </View>
              <Text style={styles.title}>{occurrence.title}</Text>
              {occurrence.description ? <Text style={styles.description}>{occurrence.description}</Text> : null}
              <Text style={styles.meta}>Registrada em {occurrence.created_at}</Text>
            </View>

            <View style={styles.card}>
              <Text style={styles.sectionTitle}>📍 Localização</Text>
              <Text style={styles.body}>{occurrence.address || 'Endereço não informado'}</Text>
              {occurrence.latitude && occurrence.longitude ? (
                <Text style={styles.meta}>
                  {occurrence.latitude.toFixed(5)}, {occurrence.longitude.toFixed(5)}
                </Text>
              ) : null}
            </View>

            <View style={styles.card}>
              <Text style={styles.sectionTitle}>👤 Solicitante</Text>
              <Text style={styles.body}>{requester?.name || 'Não identificado'}</Text>
              {requester?.email ? <Text style={styles.meta}>{requester.email}</Text> : null}
              {requester?.phone ? <Text style={styles.meta}>{requester.phone}</Text> : null}
            </View>

            <View style={styles.card}>
              <Text style={styles.sectionTitle}>🔄 Mudar status</Text>
              <View style={styles.statusGrid}>
                {statuses.map((status) => {
                  const isSelected = selectedStatusId === status.id;
                  return (
                    <TouchableOpacity
                      key={status.id}
                      style={[styles.statusOption, isSelected && styles.statusOptionSelected]}
                      onPress={() => setSelectedStatusId(status.id)}
                      accessibilityState={{ selected: isSelected }}
                    >
                      <Text style={[styles.statusOptionText, isSelected && styles.statusOptionTextSelected]}>
                        {status.name}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </View>
              <TouchableOpacity
                style={[
                  styles.saveButton,
                  (isSaving || selectedStatusId === occurrence.status_occurrences_id) && styles.saveButtonDisabled,
                ]}
                disabled={isSaving || selectedStatusId === occurrence.status_occurrences_id}
                onPress={handleChangeStatus}
              >
                {isSaving ? (
                  <ActivityIndicator color="#FFFFFF" />
                ) : (
                  <Text style={styles.saveButtonText}>Salvar status</Text>
                )}
              </TouchableOpacity>
            </View>

            <View style={styles.card}>
              <Text style={styles.sectionTitle}>⏳ Histórico</Text>
              {history.map((entry, index) => (
                <View key={`${entry.created_at}-${index}`} style={styles.historyItem}>
                  <View style={styles.historyDot} />
                  <View style={styles.historyBody}>
                    <Text style={styles.historyStatus}>{entry.status}</Text>
                    <Text style={styles.meta}>
                      {timeAgo(entry.created_at)} · {entry.changed_by ?? 'Cidadão/sistema'}
                    </Text>
                    {entry.note ? <Text style={styles.historyNote}>{entry.note}</Text> : null}
                  </View>
                </View>
              ))}
            </View>
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
  },
  topBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  backBtn: {
    minHeight: 44,
    justifyContent: 'center',
  },
  backBtnText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1E3A8A',
  },
  topBarProtocol: {
    fontSize: 14,
    fontWeight: '700',
    color: '#374151',
  },
  container: {
    padding: 16,
    gap: 12,
  },
  messageBox: {
    backgroundColor: '#ECFDF5',
    padding: 12,
    borderRadius: 8,
  },
  messageBoxError: {
    backgroundColor: '#FEE2E2',
  },
  messageText: {
    color: '#065F46',
    fontSize: 13,
    fontWeight: '600',
  },
  messageTextError: {
    color: '#B91C1C',
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  badgeRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 8,
  },
  typeBadge: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1E3A8A',
    backgroundColor: '#DBEAFE',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  statusBadge: {
    fontSize: 12,
    fontWeight: '600',
    color: '#065F46',
    backgroundColor: '#D1FAE5',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  title: {
    fontSize: 18,
    fontWeight: '800',
    color: '#111827',
  },
  description: {
    fontSize: 14,
    color: '#374151',
    marginTop: 6,
    lineHeight: 20,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 8,
  },
  body: {
    fontSize: 14,
    color: '#374151',
  },
  meta: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 4,
  },
  statusGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 12,
  },
  statusOption: {
    paddingHorizontal: 12,
    minHeight: 40,
    justifyContent: 'center',
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    backgroundColor: '#FFFFFF',
  },
  statusOptionSelected: {
    backgroundColor: '#1E3A8A',
    borderColor: '#1E3A8A',
  },
  statusOptionText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#374151',
  },
  statusOptionTextSelected: {
    color: '#FFFFFF',
  },
  saveButton: {
    height: 48,
    borderRadius: 10,
    backgroundColor: '#1E3A8A',
    justifyContent: 'center',
    alignItems: 'center',
  },
  saveButtonDisabled: {
    backgroundColor: '#93C5FD',
  },
  saveButtonText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 15,
  },
  historyItem: {
    flexDirection: 'row',
    paddingVertical: 8,
  },
  historyDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#1E3A8A',
    marginTop: 5,
    marginRight: 12,
  },
  historyBody: {
    flex: 1,
  },
  historyStatus: {
    fontSize: 14,
    fontWeight: '700',
    color: '#111827',
  },
  historyNote: {
    fontSize: 13,
    color: '#4B5563',
    marginTop: 2,
  },
});
