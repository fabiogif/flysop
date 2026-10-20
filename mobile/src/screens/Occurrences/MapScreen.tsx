import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  Image,
  ActivityIndicator,
  Linking,
  Platform,
} from 'react-native';
import { MapView, Camera, CameraRef, MarkerView } from '@maplibre/maplibre-react-native';
import * as Location from 'expo-location';
import NetInfo from '@react-native-community/netinfo';
import { apiClient } from '../../api/client';
import { Occurrence, TypeOccurrence } from '../../types';

// Tiles OpenStreetMap sem API key. ponytail: tile.openstreetmap.org não é para tráfego de produção
// alto (tile usage policy); trocar por provedor próprio/pago (MapTiler, Stadia) ao escalar.
const OSM_STYLE = {
  version: 8,
  sources: {
    osm: {
      type: 'raster',
      tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
      tileSize: 256,
      attribution: '© OpenStreetMap contributors',
    },
  },
  layers: [{ id: 'osm', type: 'raster', source: 'osm' }],
};

const DEFAULT_CENTER = { latitude: -12.9714, longitude: -38.5014 };

interface MapScreenProps {
  onSelectOccurrenceDetail: (occurrenceId: number) => void;
  onOpenNewOccurrence: () => void;
}

export const MapScreen: React.FC<MapScreenProps> = ({
  onSelectOccurrenceDetail,
  onOpenNewOccurrence,
}) => {
  const cameraRef = useRef<CameraRef>(null);
  const [userLocation, setUserLocation] = useState<{ latitude: number; longitude: number } | null>(null);
  const [occurrences, setOccurrences] = useState<Occurrence[]>([]);
  const [types, setTypes] = useState<TypeOccurrence[]>([]);
  const [selectedTypeId, setSelectedTypeId] = useState<number | null>(null);
  const [selectedOccurrence, setSelectedOccurrence] = useState<Occurrence | null>(null);

  const [hasPermission, setHasPermission] = useState<boolean | null>(null);
  const [isOffline, setIsOffline] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // Monitoramento de Conectividade Offline
  useEffect(() => {
    const unsubscribe = NetInfo.addEventListener((state) => {
      setIsOffline(!state.isConnected);
    });
    return () => unsubscribe();
  }, []);

  // Inicialização e Leitura de Localização e Ocorrências
  useEffect(() => {
    (async () => {
      try {
        const { status } = await Location.requestForegroundPermissionsAsync();
        if (status !== 'granted') {
          setHasPermission(false);
          setIsLoading(false);
          return;
        }
        setHasPermission(true);

        // Só centraliza o mapa: High esperava um fix de GPS que pode nunca vir (indoor/emulador)
        const current =
          (await Location.getLastKnownPositionAsync()) ??
          (await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.Balanced }));

        const userCoords = {
          latitude: current.coords.latitude,
          longitude: current.coords.longitude,
        };
        setUserLocation(userCoords);

        // Carregar filtros de tipos
        const typeRes = await apiClient.get<{ data?: TypeOccurrence[] } & TypeOccurrence[]>(
          '/typeOccurrence'
        );
        const typeList = typeRes.data.data ?? typeRes.data;
        if (Array.isArray(typeList)) setTypes(typeList);

        // Carregar ocorrências da região
        const occRes = await apiClient.get<{ data?: Occurrence[] } & Occurrence[]>('/occurrences');
        const occList = occRes.data.data ?? occRes.data;
        if (Array.isArray(occList)) setOccurrences(occList);
      } catch (error) {
        console.warn('MapScreen: falha ao carregar localização/ocorrências', error);
      } finally {
        setIsLoading(false);
      }
    })();
  }, []);

  // Recentralizar na posição atual
  const handleRecentre = () => {
    if (userLocation && cameraRef.current) {
      cameraRef.current.setCamera({
        centerCoordinate: [userLocation.longitude, userLocation.latitude],
        zoomLevel: 16,
        animationDuration: 500,
      });
    }
  };

  const filteredOccurrences = selectedTypeId
    ? occurrences.filter((o) => o.type_occurrences === selectedTypeId)
    : occurrences;

  if (hasPermission === false) {
    return (
      <SafeAreaView style={styles.centerBox}>
        <Text style={styles.permissionIcon}>📍</Text>
        <Text style={styles.permissionHeading}>Permissão de Localização</Text>
        <Text style={styles.permissionSub}>
          Para visualizar e apontar ocorrências no mapa da sua cidade, o aplicativo
          necessita do acesso à localização.
        </Text>
        <TouchableOpacity
          style={styles.settingsBtn}
          onPress={() => Linking.openSettings()}
        >
          <Text style={styles.settingsBtnText}>Abrir Configurações do Sistema</Text>
        </TouchableOpacity>
      </SafeAreaView>
    );
  }

  if (isLoading) {
    return (
      <SafeAreaView style={styles.centerBox}>
        <ActivityIndicator size="large" color="#1E3A8A" />
        <Text style={styles.loadingText}>Carregando mapa operacional...</Text>
      </SafeAreaView>
    );
  }

  const center = userLocation ?? DEFAULT_CENTER;

  return (
    <View style={styles.container}>
      {/* Native Map Component (MapLibre, coordenadas em [lng, lat]) */}
      <MapView
        style={styles.map}
        mapStyle={OSM_STYLE}
        compassEnabled
        logoEnabled={false}
        onPress={() => setSelectedOccurrence(null)}
      >
        <Camera
          ref={cameraRef}
          defaultSettings={{
            centerCoordinate: [center.longitude, center.latitude],
            zoomLevel: 15,
          }}
        />

        {/* Marcador Prominente da Posição do Usuário (com anel pulsante) */}
        {userLocation && (
          <MarkerView
            coordinate={[userLocation.longitude, userLocation.latitude]}
            anchor={{ x: 0.5, y: 0.5 }}
          >
            <View style={styles.userMarkerContainer} accessibilityLabel="Você está aqui">
              <View style={styles.pulsingRing} />
              <View style={styles.userDot} />
            </View>
          </MarkerView>
        )}

        {/* Marcadores de Ocorrências (Pin Designs clearly distinguishable) */}
        {filteredOccurrences.map((occ) => {
          if (!occ.latitude || !occ.longitude) return null;
          const isSelected = selectedOccurrence?.id === occ.id;
          return (
            <MarkerView
              // Remonta ao (des)selecionar: no Android o MarkerView (v10) perde a posição
              // quando o filho muda de estilo e vai para a origem do mapa
              key={`${occ.id}-${isSelected}`}
              coordinate={[occ.longitude, occ.latitude]}
            >
              <TouchableOpacity
                style={[
                  styles.occurrenceMarker,
                  isSelected && styles.occurrenceMarkerSelected,
                ]}
                onPress={() => setSelectedOccurrence(occ)}
                accessibilityLabel={occ.title}
              >
                <Text style={styles.markerEmoji}>⚠️</Text>
              </TouchableOpacity>
            </MarkerView>
          );
        })}
      </MapView>

      {/* Top Filter Chips Overlay */}
      <SafeAreaView style={styles.topOverlay}>
        {/* Offline indicator banner */}
        {isOffline && (
          <View style={styles.offlineBanner}>
            <Text style={styles.offlineBannerText}>
              ⚠️ Modo Offline: exibindo dados do mapa em cache.
            </Text>
          </View>
        )}

        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          style={styles.filtersScroll}
        >
          <TouchableOpacity
            style={[
              styles.filterChip,
              selectedTypeId === null && styles.filterChipActive,
            ]}
            onPress={() => setSelectedTypeId(null)}
          >
            <Text
              style={[
                styles.filterChipText,
                selectedTypeId === null && styles.filterChipTextActive,
              ]}
            >
              Todos
            </Text>
          </TouchableOpacity>

          {types.map((t) => (
            <TouchableOpacity
              key={t.id}
              style={[
                styles.filterChip,
                selectedTypeId === t.id && styles.filterChipActive,
              ]}
              onPress={() => setSelectedTypeId(t.id)}
            >
              <Text
                style={[
                  styles.filterChipText,
                  selectedTypeId === t.id && styles.filterChipTextActive,
                ]}
              >
                {t.name}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </SafeAreaView>

      {/* Floating Action Buttons: Recentre & New Occurrence */}
      <View style={styles.floatingControls}>
        <TouchableOpacity
          style={styles.floatingBtn}
          onPress={handleRecentre}
          accessibilityLabel="Recentralizar na minha localização"
        >
          <Text style={styles.floatingBtnText}>🎯</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.floatingBtn, styles.newOccurrenceBtn]}
          onPress={onOpenNewOccurrence}
          accessibilityLabel="Criar nova ocorrência"
        >
          <Text style={styles.newOccurrenceBtnText}>＋</Text>
        </TouchableOpacity>
      </View>

      {/* Selected Item Bottom Sheet */}
      {selectedOccurrence && (
        <View style={styles.bottomSheet}>
          <View style={styles.sheetHandle} />

          <View style={styles.sheetContent}>
            <View style={styles.sheetTextCol}>
              <View style={styles.sheetBadgeRow}>
                <Text style={styles.protocolBadge}>
                  {selectedOccurrence.protocol || 'OC-2026'}
                </Text>
                <Text style={styles.statusBadge}>
                  {selectedOccurrence.nameStatus || 'Em Análise'}
                </Text>
              </View>

              <Text style={styles.sheetTitle} numberOfLines={1}>
                {selectedOccurrence.title}
              </Text>
              <Text style={styles.sheetAddress} numberOfLines={1}>
                📍 {selectedOccurrence.address || 'Endereço registrado'}
              </Text>
            </View>

            {selectedOccurrence.anexo && selectedOccurrence.anexo[0] && (
              <Image
                source={{ uri: selectedOccurrence.anexo[0] }}
                style={styles.sheetThumb}
              />
            )}
          </View>

          <TouchableOpacity
            style={styles.detailsBtn}
            onPress={() => {
              onSelectOccurrenceDetail(selectedOccurrence.id);
            }}
          >
            <Text style={styles.detailsBtnText}>Ver Detalhes do Chamado</Text>
          </TouchableOpacity>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  map: {
    ...StyleSheet.absoluteFillObject,
  },
  topOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
  },
  offlineBanner: {
    backgroundColor: '#FEF3C7',
    paddingVertical: 8,
    paddingHorizontal: 16,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#F59E0B',
  },
  offlineBannerText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#92400E',
  },
  filtersScroll: {
    paddingHorizontal: 16,
    paddingTop: 8,
  },
  filterChip: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    marginRight: 8,
    shadowColor: '#000',
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    minHeight: 44,
    justifyContent: 'center',
  },
  filterChipActive: {
    backgroundColor: '#1E3A8A',
  },
  filterChipText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#374151',
  },
  filterChipTextActive: {
    color: '#FFFFFF',
  },
  userMarkerContainer: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  pulsingRing: {
    position: 'absolute',
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: 'rgba(59, 130, 246, 0.3)',
    borderWidth: 1.5,
    borderColor: '#3B82F6',
  },
  userDot: {
    width: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: '#1E3A8A',
    borderWidth: 2,
    borderColor: '#FFFFFF',
  },
  occurrenceMarker: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#FFFFFF',
    borderWidth: 2,
    borderColor: '#DC2626',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 4,
  },
  occurrenceMarkerSelected: {
    borderColor: '#1E3A8A',
    transform: [{ scale: 1.15 }],
  },
  markerEmoji: {
    fontSize: 18,
  },
  floatingControls: {
    position: 'absolute',
    right: 16,
    bottom: 30,
    gap: 12,
  },
  floatingBtn: {
    width: 52,
    height: 52,
    borderRadius: 26,
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOpacity: 0.15,
    shadowRadius: 6,
    elevation: 5,
  },
  floatingBtnText: {
    fontSize: 22,
  },
  newOccurrenceBtn: {
    backgroundColor: '#1E3A8A',
  },
  newOccurrenceBtnText: {
    color: '#FFFFFF',
    fontSize: 28,
    fontWeight: '700',
    lineHeight: 32,
  },
  bottomSheet: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingHorizontal: 20,
    paddingTop: 10,
    paddingBottom: Platform.OS === 'ios' ? 34 : 20,
    shadowColor: '#000',
    shadowOpacity: 0.12,
    shadowRadius: 10,
    elevation: 10,
  },
  sheetHandle: {
    width: 40,
    height: 4,
    backgroundColor: '#D1D5DB',
    borderRadius: 2,
    alignSelf: 'center',
    marginBottom: 12,
  },
  sheetContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 14,
  },
  sheetTextCol: {
    flex: 1,
    paddingRight: 12,
  },
  sheetBadgeRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 4,
  },
  protocolBadge: {
    fontSize: 11,
    fontWeight: '700',
    color: '#1E3A8A',
    backgroundColor: '#DBEAFE',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  statusBadge: {
    fontSize: 11,
    fontWeight: '600',
    color: '#065F46',
    backgroundColor: '#D1FAE5',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
  },
  sheetTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#111827',
  },
  sheetAddress: {
    fontSize: 13,
    color: '#6B7280',
    marginTop: 2,
  },
  sheetThumb: {
    width: 60,
    height: 60,
    borderRadius: 8,
  },
  detailsBtn: {
    height: 46,
    backgroundColor: '#1E3A8A',
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  detailsBtnText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 14,
  },
  centerBox: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
    backgroundColor: '#FFFFFF',
  },
  permissionIcon: {
    fontSize: 54,
    marginBottom: 16,
  },
  permissionHeading: {
    fontSize: 20,
    fontWeight: '800',
    color: '#111827',
    marginBottom: 8,
  },
  permissionSub: {
    fontSize: 14,
    color: '#4B5563',
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 24,
  },
  settingsBtn: {
    height: 48,
    backgroundColor: '#1E3A8A',
    paddingHorizontal: 24,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  settingsBtnText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 15,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: '#6B7280',
  },
});
