import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  Image,
  ActivityIndicator,
} from 'react-native';
import { apiClient } from '../../api/client';
import { Occurrence } from '../../types';

interface OccurrenceDetailScreenProps {
  occurrenceId: number;
  onBack: () => void;
}

export const OccurrenceDetailScreen: React.FC<OccurrenceDetailScreenProps> = ({
  occurrenceId,
  onBack,
}) => {
  const [occurrence, setOccurrence] = useState<Occurrence | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    (async () => {
      try {
        const response = await apiClient.get<{ data: Occurrence }>(`/occurrences/${occurrenceId}`);
        setOccurrence(response.data.data);
      } catch {
      } finally {
        setIsLoading(false);
      }
    })();
  }, [occurrenceId]);

  if (isLoading) {
    return (
      <SafeAreaView style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#1E3A8A" />
      </SafeAreaView>
    );
  }

  if (!occurrence) {
    return (
      <SafeAreaView style={styles.centerContainer}>
        <Text style={styles.errorText}>Ocorrência não encontrada.</Text>
        <TouchableOpacity style={styles.backBtn} onPress={onBack}>
          <Text style={styles.backBtnText}>Voltar</Text>
        </TouchableOpacity>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.topHeader}>
        <TouchableOpacity style={styles.backTouch} onPress={onBack}>
          <Text style={styles.backArrow}>←</Text>
          <Text style={styles.backLabel}>Voltar</Text>
        </TouchableOpacity>
        <Text style={styles.headerProtocol}>{occurrence.protocol || 'OC-2026'}</Text>
      </View>

      <ScrollView contentContainerStyle={styles.container}>
        {/* Title and Category */}
        <View style={styles.cardSection}>
          <View style={styles.badgeRow}>
            <Text style={styles.typeBadge}>
              {occurrence.nameType || 'Zeladoria'}
            </Text>
            <Text style={styles.statusBadge}>
              {occurrence.nameStatus || 'Em Andamento'}
            </Text>
          </View>
          <Text style={styles.title}>{occurrence.title}</Text>
          {occurrence.description ? (
            <Text style={styles.description}>{occurrence.description}</Text>
          ) : null}
        </View>

        {/* Location Box */}
        <View style={styles.cardSection}>
          <Text style={styles.sectionTitle}>📍 Localização</Text>
          <Text style={styles.addressMain}>
            {occurrence.address || 'Endereço registrado no mapa'}
          </Text>
          {occurrence.neighborhood || occurrence.city ? (
            <Text style={styles.addressSub}>
              {occurrence.neighborhood ? `${occurrence.neighborhood}, ` : ''}
              {occurrence.city} - {occurrence.state}
            </Text>
          ) : null}
          {occurrence.latitude && occurrence.longitude ? (
            <Text style={styles.coords}>
              Coordenadas: {occurrence.latitude.toFixed(5)},{' '}
              {occurrence.longitude.toFixed(5)}
            </Text>
          ) : null}
        </View>

        {/* Status Timeline */}
        <View style={styles.cardSection}>
          <Text style={styles.sectionTitle}>⏳ Linha do Tempo do Atendimento</Text>
          <View style={styles.timelineContainer}>
            <View style={styles.timelineItem}>
              <View style={[styles.timelineDot, styles.timelineDotActive]} />
              <View style={styles.timelineContent}>
                <Text style={styles.timelineStepTitle}>Ocorrência Registrada</Text>
                <Text style={styles.timelineStepDesc}>
                  Chamado aberto pelo cidadão e aguardando triagem.
                </Text>
              </View>
            </View>

            <View style={styles.timelineItem}>
              <View style={[styles.timelineDot, styles.timelineDotActive]} />
              <View style={styles.timelineContent}>
                <Text style={styles.timelineStepTitle}>Em Análise pelo Órgão</Text>
                <Text style={styles.timelineStepDesc}>
                  Solicitação recebida e equipe operacional despachada.
                </Text>
              </View>
            </View>

            <View style={styles.timelineItem}>
              <View style={styles.timelineDot} />
              <View style={styles.timelineContent}>
                <Text style={styles.timelineStepTitle}>Serviço Concluído</Text>
                <Text style={styles.timelineStepDesc}>
                  Reparo efetuado e verificado no local.
                </Text>
              </View>
            </View>
          </View>
        </View>

        {/* Evidences (Antes / Depois) */}
        {occurrence.anexo && occurrence.anexo.length > 0 && (
          <View style={styles.cardSection}>
            <Text style={styles.sectionTitle}>📷 Fotos de Evidência</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.photosScroll}>
              {occurrence.anexo.map((url, idx) => (
                <View key={idx} style={styles.photoBox}>
                  <Image source={{ uri: url }} style={styles.evidenceImage} />
                  <Text style={styles.photoLabel}>Evidência #{idx + 1}</Text>
                </View>
              ))}
            </ScrollView>
          </View>
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
  topHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  backTouch: {
    flexDirection: 'row',
    alignItems: 'center',
    minHeight: 44,
  },
  backArrow: {
    fontSize: 20,
    color: '#1E3A8A',
    marginRight: 6,
  },
  backLabel: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1E3A8A',
  },
  headerProtocol: {
    fontSize: 14,
    fontWeight: '800',
    color: '#374151',
  },
  container: {
    padding: 16,
    gap: 14,
  },
  cardSection: {
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
    backgroundColor: '#EFF6FF',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  statusBadge: {
    fontSize: 12,
    fontWeight: '700',
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
    marginBottom: 6,
  },
  description: {
    fontSize: 14,
    color: '#4B5563',
    lineHeight: 20,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 10,
  },
  addressMain: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1F2937',
  },
  addressSub: {
    fontSize: 13,
    color: '#6B7280',
    marginTop: 2,
  },
  coords: {
    fontSize: 11,
    color: '#9CA3AF',
    marginTop: 4,
  },
  timelineContainer: {
    borderLeftWidth: 2,
    borderLeftColor: '#E5E7EB',
    marginLeft: 8,
    paddingLeft: 14,
    gap: 16,
  },
  timelineItem: {
    position: 'relative',
  },
  timelineDot: {
    position: 'absolute',
    left: -21,
    top: 2,
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: '#D1D5DB',
  },
  timelineDotActive: {
    backgroundColor: '#1E3A8A',
  },
  timelineContent: {},
  timelineStepTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#1F2937',
  },
  timelineStepDesc: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 2,
  },
  photosScroll: {
    flexDirection: 'row',
  },
  photoBox: {
    marginRight: 12,
  },
  evidenceImage: {
    width: 120,
    height: 120,
    borderRadius: 10,
  },
  photoLabel: {
    fontSize: 11,
    color: '#6B7280',
    marginTop: 4,
    textAlign: 'center',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    padding: 24,
  },
  errorText: {
    fontSize: 15,
    color: '#DC2626',
    marginBottom: 16,
  },
  backBtn: {
    backgroundColor: '#1E3A8A',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 8,
  },
  backBtnText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
});
