import React, { useRef } from 'react';
import { Modal, View, Text, TouchableOpacity, StyleSheet, SafeAreaView } from 'react-native';
import { MapView, MapViewRef, Camera } from '@maplibre/maplibre-react-native';
import { OSM_STYLE, DEFAULT_CENTER, Coordinates } from './osmStyle';

interface LocationPickerModalProps {
  visible: boolean;
  initial: Coordinates | null;
  onConfirm: (coords: Coordinates) => void;
  onClose: () => void;
}

// Escolha de um ponto qualquer: o pin fica fixo no centro e o usuário arrasta o mapa até o local
export const LocationPickerModal: React.FC<LocationPickerModalProps> = ({
  visible,
  initial,
  onConfirm,
  onClose,
}) => {
  const mapRef = useRef<MapViewRef>(null);
  const start = initial ?? DEFAULT_CENTER;

  const handleConfirm = async () => {
    const center = await mapRef.current?.getCenter();
    if (center) onConfirm({ longitude: center[0], latitude: center[1] });
  };

  return (
    <Modal visible={visible} animationType="slide" onRequestClose={onClose}>
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <Text style={styles.title}>Escolha o local no mapa</Text>
          <Text style={styles.subtitle}>Arraste o mapa até o pin ficar sobre o problema.</Text>
        </View>

        <View style={styles.mapWrapper}>
          <MapView ref={mapRef} style={styles.map} mapStyle={OSM_STYLE} logoEnabled={false}>
            <Camera
              defaultSettings={{ centerCoordinate: [start.longitude, start.latitude], zoomLevel: 17 }}
            />
          </MapView>
          {/* Pin fixo: a ponta marca o centro do mapa */}
          <View pointerEvents="none" style={styles.centerPin}>
            <Text style={styles.centerPinIcon}>📍</Text>
          </View>
        </View>

        <View style={styles.actions}>
          <TouchableOpacity style={styles.cancelBtn} onPress={onClose}>
            <Text style={styles.cancelText}>Cancelar</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.confirmBtn} onPress={handleConfirm}>
            <Text style={styles.confirmText}>Usar este local</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    </Modal>
  );
};

const PIN_SIZE = 44;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 12,
  },
  title: {
    fontSize: 18,
    fontWeight: '800',
    color: '#111827',
  },
  subtitle: {
    fontSize: 13,
    color: '#6B7280',
    marginTop: 4,
  },
  mapWrapper: {
    flex: 1,
  },
  map: {
    flex: 1,
  },
  centerPin: {
    position: 'absolute',
    left: '50%',
    top: '50%',
    marginLeft: -PIN_SIZE / 2,
    marginTop: -PIN_SIZE, // a ponta do emoji fica no centro
    width: PIN_SIZE,
    height: PIN_SIZE,
    alignItems: 'center',
    justifyContent: 'flex-end',
  },
  centerPinIcon: {
    fontSize: 38,
    lineHeight: PIN_SIZE,
  },
  actions: {
    flexDirection: 'row',
    gap: 12,
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
  },
  cancelBtn: {
    flex: 1,
    height: 48,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: '#D1D5DB',
    justifyContent: 'center',
    alignItems: 'center',
  },
  cancelText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#374151',
  },
  confirmBtn: {
    flex: 2,
    height: 48,
    borderRadius: 10,
    backgroundColor: '#1E3A8A',
    justifyContent: 'center',
    alignItems: 'center',
  },
  confirmText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFFFFF',
  },
});
