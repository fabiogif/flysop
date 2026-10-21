import React, { useState, useEffect, useCallback } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  Image,
  ActivityIndicator,
  Switch,
} from 'react-native';
import * as Location from 'expo-location';
import { apiClient, getApiErrorMessage } from '../../api/client';
import { TypeOccurrence } from '../../types';
import { toUf } from '../../utils/uf';
import { useAuth } from '../../context/AuthContext';
import { saveGuestProtocol } from '../../services/guestProtocols';
import { AppCameraModal } from '../../components/Camera/AppCameraModal';
import { LocationPickerModal } from '../../components/Map/LocationPickerModal';
import { Coordinates } from '../../components/Map/osmStyle';

interface NewOccurrenceScreenProps {
  onNavigateHome: () => void;
  // Ponto escolhido no mapa principal (toque longo); sem ele, usa o GPS
  initialPoint?: Coordinates | null;
}

const TYPES_ERROR = 'Não foi possível carregar os tipos de ocorrência. Verifique sua conexão.';

export const NewOccurrenceScreen: React.FC<NewOccurrenceScreenProps> = ({
  onNavigateHome,
  initialPoint,
}) => {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [types, setTypes] = useState<TypeOccurrence[]>([]);
  const [selectedTypeId, setSelectedTypeId] = useState<number | null>(null);

  // Localização
  const [latitude, setLatitude] = useState<number | null>(null);
  const [longitude, setLongitude] = useState<number | null>(null);
  const [address, setAddress] = useState('');
  const [neighborhood, setNeighborhood] = useState('');
  const [city, setCity] = useState('');
  const [state, setState] = useState('');
  const [isGpsLoading, setIsGpsLoading] = useState(false);
  const [locationSource, setLocationSource] = useState<'gps' | 'map'>('gps');
  const [isPickerOpen, setIsPickerOpen] = useState(false);

  // Fotos e Câmera
  const [photoUri, setPhotoUri] = useState<string | null>(null);
  const [isCameraOpen, setIsCameraOpen] = useState(false);

  // LGPD & Anonimato
  const [lgpdConsent, setLgpdConsent] = useState(false);
  const [isAnonymous, setIsAnonymous] = useState(false);
  // Sem cadastro a ocorrência é sempre anônima (o backend também garante)
  const { isGuest } = useAuth();
  const sendAnonymous = isGuest || isAnonymous;

  // Status de Submissão
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [generatedProtocol, setGeneratedProtocol] = useState<string | null>(null);
  const [trackingCode, setTrackingCode] = useState<string | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const loadTypes = useCallback(async () => {
    try {
      const response = await apiClient.get<{ data?: TypeOccurrence[] } & TypeOccurrence[]>(
        '/typeOccurrence'
      );
      const list = response.data.data ?? response.data;
      if (Array.isArray(list)) {
        setTypes(list);
        setSelectedTypeId((current) => current ?? list[0]?.id ?? null);
        setErrorMessage((current) => (current === TYPES_ERROR ? null : current));
      }
    } catch (error) {
      // Sem tipos o chamado não pode ser enviado: avisa em vez de deixar o campo vazio
      console.warn('NewOccurrence: falha ao carregar tipos', error);
      setErrorMessage(TYPES_ERROR);
    }
  }, []);

  // A aba fica montada: se a 1ª carga falhou (ex.: API acordando), tenta de novo ao voltar à tela
  useFocusEffect(
    useCallback(() => {
      if (types.length === 0) loadTypes();
    }, [types.length, loadTypes])
  );

  useEffect(() => {
    // Localização inicial: o ponto vindo do mapa tem prioridade sobre o GPS
    if (!initialPoint) detectGpsLocation();
  }, []);

  useEffect(() => {
    if (initialPoint) applyCoordinates(initialPoint, 'map');
  }, [initialPoint]);

  // Ponto único para GPS e mapa: grava as coordenadas e preenche o endereço pelo geocoding reverso
  const applyCoordinates = async (coords: Coordinates, source: 'gps' | 'map') => {
    setLatitude(coords.latitude);
    setLongitude(coords.longitude);
    setLocationSource(source);
    // Limpa o endereço anterior para não ficar um endereço de outro ponto
    setAddress('');
    setNeighborhood('');
    setCity('');
    setState('');
    try {
      const [geo] = await Location.reverseGeocodeAsync(coords);
      if (geo) {
        setAddress(geo.street ? `${geo.street}, ${geo.streetNumber || 'S/N'}` : '');
        setNeighborhood(geo.district || geo.subregion || '');
        setCity(geo.city || '');
        setState(toUf(geo.region));
      }
    } catch (error) {
      console.warn('NewOccurrence: falha no geocoding reverso', error);
    }
  };

  const detectGpsLocation = async () => {
    setIsGpsLoading(true);
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') return;

      const loc = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.High,
      });
      await applyCoordinates(loc.coords, 'gps');
    } catch (error) {
      console.warn('NewOccurrence: falha ao obter GPS', error);
    } finally {
      setIsGpsLoading(false);
    }
  };

  const handlePickOnMap = async (coords: Coordinates) => {
    setIsPickerOpen(false);
    await applyCoordinates(coords, 'map');
  };

  const isFormValid =
    title.trim().length >= 3 &&
    selectedTypeId !== null &&
    lgpdConsent &&
    !isSubmitting;

  const handleSubmit = async () => {
    if (!isFormValid) return;
    setIsSubmitting(true);
    setErrorMessage(null);

    try {
      const formData = new FormData();
      formData.append('title', title);
      formData.append('type_occurrences_id', String(selectedTypeId));
      formData.append('lgpd_consent', '1');
      if (description) formData.append('description', description);
      if (latitude) formData.append('latitude', String(latitude));
      if (longitude) formData.append('longitude', String(longitude));
      if (address) formData.append('address', address);
      if (neighborhood) formData.append('neighborhood', neighborhood);
      if (city) formData.append('city', city);
      if (state) formData.append('state', state);
      if (sendAnonymous) formData.append('is_anonymous', '1');

      if (photoUri) {
        const filename = photoUri.split('/').pop() ?? 'foto.jpg';
        const match = /\.(\w+)$/.exec(filename);
        const type = match ? `image/${match[1]}` : 'image/jpeg';

        formData.append('anexo[]', {
          uri: photoUri,
          name: filename,
          type,
        } as unknown as Blob);
      }

      const response = await apiClient.post<{
        data?: { protocol?: string };
        protocol?: string;
        tracking_code?: string;
      }>('/occurrences', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      const protocol =
        response.data.data?.protocol ?? response.data.protocol ?? 'OC-REGISTRADA';
      // Sem conta, a API devolve o código de acompanhamento (só agora): sem ele não há como ver
      const code = response.data.tracking_code ?? null;
      if (isGuest) await saveGuestProtocol(protocol, code ?? undefined);
      setTrackingCode(code);
      setGeneratedProtocol(protocol);
    } catch (err: unknown) {
      setErrorMessage(getApiErrorMessage(err) ?? 'Erro ao registrar ocorrência. Tente novamente.');
    } finally {
      setIsSubmitting(false);
    }
  };

  // A aba mantém o estado: limpa para a próxima ocorrência (localização e tipo continuam)
  const handleBackHome = () => {
    setGeneratedProtocol(null);
    setTrackingCode(null);
    setTitle('');
    setDescription('');
    setPhotoUri(null);
    setLgpdConsent(false);
    setIsAnonymous(false);
    setErrorMessage(null);
    onNavigateHome();
  };

  // Sucesso com exibição do Protocolo
  if (generatedProtocol) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <View style={styles.successContainer}>
          <Text style={styles.successIcon}>✅</Text>
          <Text style={styles.successTitle}>Ocorrência Registrada!</Text>
          <Text style={styles.successSubtitle}>
            Sua solicitação foi enviada para o setor responsável da prefeitura.
          </Text>

          <View style={styles.protocolCard}>
            <Text style={styles.protocolLabel}>NÚMERO DE PROTOCOLO</Text>
            <Text style={styles.protocolValue} selectable>{generatedProtocol}</Text>
            {trackingCode ? (
              <>
                <Text style={[styles.protocolLabel, styles.trackingCodeLabel]}>
                  CÓDIGO DE ACOMPANHAMENTO
                </Text>
                <Text style={styles.protocolValue} selectable>{trackingCode}</Text>
                <Text style={styles.trackingCodeHint}>
                  Anote o protocolo e o código: só com os dois é possível acompanhar de outro
                  aparelho.
                </Text>
              </>
            ) : null}
          </View>

          <TouchableOpacity
            style={styles.backHomeButton}
            onPress={handleBackHome}
          >
            <Text style={styles.backHomeButtonText}>Voltar ao Início</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.container}>
        <Text style={styles.heading}>Nova Ocorrência</Text>
        <Text style={styles.subheading}>
          Descreva o problema e aponte a localização no mapa.
        </Text>

        {errorMessage && (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{errorMessage}</Text>
            {errorMessage === TYPES_ERROR ? (
              <TouchableOpacity onPress={loadTypes} accessibilityRole="button">
                <Text style={styles.retryText}>Tentar novamente</Text>
              </TouchableOpacity>
            ) : null}
          </View>
        )}

        {/* Localização GPS */}
        <View style={styles.gpsCard}>
          <View style={styles.gpsHeader}>
            <Text style={styles.gpsTitle}>📍 Local do Problema</Text>
            <Text style={styles.locationSourceText}>
              {locationSource === 'map' ? 'Ponto escolhido no mapa' : 'Sua localização atual'}
            </Text>
          </View>

          {latitude && longitude ? (
            <View>
              <Text style={styles.addressText}>
                {address || 'Coordenadas capturadas'}
              </Text>
              <Text style={styles.coordsText}>
                {neighborhood ? `${neighborhood}, ` : ''}
                {city ? `${city} - ` : ''}
                {state} ({latitude.toFixed(5)}, {longitude.toFixed(5)})
              </Text>
            </View>
          ) : (
            <Text style={styles.noGpsText}>
              {isGpsLoading ? 'Buscando localização exata via GPS...' : 'Nenhum local definido.'}
            </Text>
          )}

          {/* Um ou outro: a localização atual ou qualquer ponto escolhido no mapa */}
          <View style={styles.locationActions}>
            <TouchableOpacity
              style={[styles.locationBtn, locationSource === 'gps' && styles.locationBtnActive]}
              onPress={detectGpsLocation}
              disabled={isGpsLoading}
              accessibilityRole="button"
            >
              <Text style={[styles.locationBtnText, locationSource === 'gps' && styles.locationBtnTextActive]}>
                {isGpsLoading ? 'Localizando...' : '🎯 Minha localização'}
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.locationBtn, locationSource === 'map' && styles.locationBtnActive]}
              onPress={() => setIsPickerOpen(true)}
              accessibilityRole="button"
            >
              <Text style={[styles.locationBtnText, locationSource === 'map' && styles.locationBtnTextActive]}>
                🗺️ Escolher no mapa
              </Text>
            </TouchableOpacity>
          </View>
        </View>

        <LocationPickerModal
          visible={isPickerOpen}
          initial={latitude && longitude ? { latitude, longitude } : null}
          onConfirm={handlePickOnMap}
          onClose={() => setIsPickerOpen(false)}
        />

        {/* Tipo de Ocorrência */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Tipo de Ocorrência *</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipsScroll}>
            {types.map((type) => (
              <TouchableOpacity
                key={type.id}
                style={[
                  styles.chip,
                  selectedTypeId === type.id && styles.chipActive,
                ]}
                onPress={() => setSelectedTypeId(type.id)}
              >
                <Text
                  style={[
                    styles.chipText,
                    selectedTypeId === type.id && styles.chipTextActive,
                  ]}
                >
                  {type.name}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>

        {/* Título do Problema */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Título do Chamado *</Text>
          <TextInput
            style={styles.input}
            placeholder="Ex: Buraco profundo na via"
            value={title}
            onChangeText={setTitle}
          />
        </View>

        {/* Descrição Detalhada */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Descrição Detalhada</Text>
          <TextInput
            style={[styles.input, styles.textArea]}
            placeholder="Informe pontos de referência ou detalhes que ajudem a equipe..."
            value={description}
            onChangeText={setDescription}
            multiline
            numberOfLines={3}
          />
        </View>

        {/* Foto de Evidência */}
        <View style={styles.formGroup}>
          <Text style={styles.label}>Foto de Evidência</Text>
          {photoUri ? (
            <View style={styles.photoPreviewBox}>
              <Image source={{ uri: photoUri }} style={styles.photoThumb} />
              <TouchableOpacity
                style={styles.changePhotoBtn}
                onPress={() => setIsCameraOpen(true)}
              >
                <Text style={styles.changePhotoText}>Alterar Foto</Text>
              </TouchableOpacity>
            </View>
          ) : (
            <TouchableOpacity
              style={styles.addPhotoBtn}
              onPress={() => setIsCameraOpen(true)}
            >
              <Text style={styles.addPhotoIcon}>📷</Text>
              <Text style={styles.addPhotoText}>Tirar Foto da Ocorrência</Text>
            </TouchableOpacity>
          )}
        </View>

        {/* Opção Anônima */}
        <View style={styles.switchRow}>
          <View style={styles.switchTextContainer}>
            <Text style={styles.switchTitle}>Enviar de forma anônima</Text>
            <Text style={styles.switchSubtitle}>
              {isGuest
                ? 'Sem cadastro, o chamado é sempre anônimo. Guarde o protocolo e o código para acompanhar.'
                : 'O órgão não verá seu nome; você continua acompanhando em Minhas Ocorrências.'}
            </Text>
          </View>
          <Switch
            value={sendAnonymous}
            onValueChange={setIsAnonymous}
            disabled={isGuest}
            trackColor={{ false: '#D1D5DB', true: '#93C5FD' }}
            thumbColor={sendAnonymous ? '#1E3A8A' : '#F3F4F6'}
            accessibilityLabel="Enviar de forma anônima"
          />
        </View>

        {/* Termo de Consentimento LGPD */}
        <TouchableOpacity
          style={styles.checkboxRow}
          onPress={() => setLgpdConsent(!lgpdConsent)}
        >
          <View
            style={[styles.checkbox, lgpdConsent && styles.checkboxChecked]}
          >
            {lgpdConsent && <Text style={styles.checkmark}>✓</Text>}
          </View>
          <Text style={styles.checkboxLabel}>
            Confirmo que as informações prestadas são verdadeiras e autorizo o
            tratamento de dados para o atendimento do chamado público (LGPD).*
          </Text>
        </TouchableOpacity>

        {/* Botão de Envio */}
        <TouchableOpacity
          style={[
            styles.submitButton,
            (!isFormValid || isSubmitting) && styles.submitButtonDisabled,
          ]}
          disabled={!isFormValid || isSubmitting}
          onPress={handleSubmit}
        >
          {isSubmitting ? (
            <ActivityIndicator color="#FFFFFF" />
          ) : (
            <Text style={styles.submitButtonText}>Registrar Chamado</Text>
          )}
        </TouchableOpacity>
      </ScrollView>

      {/* Modal de Câmera */}
      <AppCameraModal
        visible={isCameraOpen}
        onClose={() => setIsCameraOpen(false)}
        onPhotoConfirmed={(uri) => setPhotoUri(uri)}
      />
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  retryText: {
    color: '#1E3A8A',
    fontWeight: '700',
    marginTop: 6,
  },
  safeArea: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  container: {
    padding: 20,
    paddingBottom: 40,
  },
  heading: {
    fontSize: 22,
    fontWeight: '800',
    color: '#111827',
  },
  subheading: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 4,
    marginBottom: 18,
  },
  errorBox: {
    backgroundColor: '#FEE2E2',
    padding: 12,
    borderRadius: 8,
    marginBottom: 16,
  },
  errorText: {
    color: '#DC2626',
    fontSize: 13,
  },
  gpsCard: {
    backgroundColor: '#F3F4F6',
    padding: 14,
    borderRadius: 12,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  gpsHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  gpsTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#1E3A8A',
  },
  locationSourceText: {
    fontSize: 12,
    color: '#6B7280',
    fontWeight: '600',
  },
  locationActions: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
  },
  locationBtn: {
    flex: 1,
    minHeight: 44,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: '#1E3A8A',
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 8,
  },
  locationBtnActive: {
    backgroundColor: '#1E3A8A',
  },
  locationBtnText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1E3A8A',
  },
  locationBtnTextActive: {
    color: '#FFFFFF',
  },
  addressText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#111827',
  },
  coordsText: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 2,
  },
  noGpsText: {
    fontSize: 13,
    color: '#6B7280',
    fontStyle: 'italic',
  },
  formGroup: {
    marginBottom: 18,
  },
  label: {
    fontSize: 14,
    fontWeight: '700',
    color: '#374151',
    marginBottom: 8,
  },
  input: {
    height: 48,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 10,
    paddingHorizontal: 14,
    fontSize: 15,
    color: '#111827',
    backgroundColor: '#F9FAFB',
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
    paddingTop: 10,
  },
  chipsScroll: {
    flexDirection: 'row',
  },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#F3F4F6',
    marginRight: 8,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    minHeight: 44,
    justifyContent: 'center',
  },
  chipActive: {
    backgroundColor: '#1E3A8A',
    borderColor: '#1E3A8A',
  },
  chipText: {
    fontSize: 13,
    color: '#4B5563',
    fontWeight: '500',
  },
  chipTextActive: {
    color: '#FFFFFF',
    fontWeight: '700',
  },
  addPhotoBtn: {
    height: 90,
    borderWidth: 1.5,
    borderStyle: 'dashed',
    borderColor: '#9CA3AF',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F9FAFB',
  },
  addPhotoIcon: {
    fontSize: 28,
    marginBottom: 4,
  },
  addPhotoText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4B5563',
  },
  photoPreviewBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 16,
  },
  photoThumb: {
    width: 80,
    height: 80,
    borderRadius: 10,
  },
  changePhotoBtn: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    backgroundColor: '#F3F4F6',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D1D5DB',
  },
  changePhotoText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#374151',
  },
  switchRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 12,
    borderTopWidth: 1,
    borderTopColor: '#F3F4F6',
    marginBottom: 14,
  },
  switchTextContainer: {
    flex: 1,
    paddingRight: 16,
  },
  switchTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  switchSubtitle: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 2,
  },
  checkboxRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 24,
  },
  checkbox: {
    width: 20,
    height: 20,
    borderWidth: 1.5,
    borderColor: '#9CA3AF',
    borderRadius: 5,
    marginRight: 10,
    marginTop: 2,
    justifyContent: 'center',
    alignItems: 'center',
  },
  checkboxChecked: {
    backgroundColor: '#1E3A8A',
    borderColor: '#1E3A8A',
  },
  checkmark: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '800',
  },
  checkboxLabel: {
    flex: 1,
    fontSize: 13,
    color: '#4B5563',
    lineHeight: 18,
  },
  submitButton: {
    height: 52,
    backgroundColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  submitButtonDisabled: {
    backgroundColor: '#93C5FD',
  },
  submitButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
  successContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  successIcon: {
    fontSize: 64,
    marginBottom: 16,
  },
  successTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: '#111827',
    textAlign: 'center',
    marginBottom: 8,
  },
  successSubtitle: {
    fontSize: 15,
    color: '#4B5563',
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 24,
  },
  protocolCard: {
    backgroundColor: '#EFF6FF',
    borderWidth: 1.5,
    borderColor: '#3B82F6',
    borderRadius: 12,
    padding: 20,
    alignItems: 'center',
    width: '100%',
    marginBottom: 32,
  },
  trackingCodeLabel: {
    marginTop: 16,
  },
  trackingCodeHint: {
    marginTop: 10,
    fontSize: 13,
    color: '#4B5563',
    textAlign: 'center',
  },
  protocolLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1E3A8A',
    letterSpacing: 1,
    marginBottom: 6,
  },
  protocolValue: {
    fontSize: 26,
    fontWeight: '900',
    color: '#1E3A8A',
  },
  backHomeButton: {
    width: '100%',
    height: 52,
    backgroundColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  backHomeButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
});
