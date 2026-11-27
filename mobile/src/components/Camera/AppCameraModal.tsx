import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Modal,
  Image,
  FlatList,
  SafeAreaView,
} from 'react-native';
import { CameraView, CameraType, FlashMode, useCameraPermissions } from 'expo-camera';

interface AppCameraModalProps {
  visible: boolean;
  onClose: () => void;
  onPhotoConfirmed: (photoUri: string) => void;
}

export const AppCameraModal: React.FC<AppCameraModalProps> = ({
  visible,
  onClose,
  onPhotoConfirmed,
}) => {
  const [permission, requestPermission] = useCameraPermissions();
  const [facing, setFacing] = useState<CameraType>('back');
  const [flash, setFlash] = useState<FlashMode>('off');
  const [capturedUri, setCapturedUri] = useState<string | null>(null);
  const [sessionCaptures, setSessionCaptures] = useState<string[]>([]);
  const cameraRef = useRef<CameraView>(null);

  if (!visible) return null;

  if (!permission?.granted) {
    return (
      <Modal visible={visible} animationType="slide">
        <SafeAreaView style={styles.permissionContainer}>
          <Text style={styles.permissionTitle}>Permissão de Câmera</Text>
          <Text style={styles.permissionSubtitle}>
            Precisamos do acesso à sua câmera para registrar a evidência fotográfica da
            ocorrência.
          </Text>
          <TouchableOpacity style={styles.confirmButton} onPress={requestPermission}>
            <Text style={styles.confirmButtonText}>Autorizar Câmera</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.cancelLink} onPress={onClose}>
            <Text style={styles.cancelLinkText}>Cancelar</Text>
          </TouchableOpacity>
        </SafeAreaView>
      </Modal>
    );
  }

  const handleCapture = async () => {
    if (!cameraRef.current) return;
    try {
      const photo = await cameraRef.current.takePictureAsync({ quality: 0.75 });
      if (photo?.uri) {
        setCapturedUri(photo.uri);
        setSessionCaptures((prev) => [photo.uri, ...prev]);
      }
    } catch {}
  };

  const cycleFlash = () => {
    const next: FlashMode = flash === 'off' ? 'on' : flash === 'on' ? 'auto' : 'off';
    setFlash(next);
  };

  const toggleCameraFacing = () => {
    setFacing((prev) => (prev === 'back' ? 'front' : 'back'));
  };

  return (
    <Modal visible={visible} animationType="slide">
      <View style={styles.container}>
        {capturedUri ? (
          /* Post-capture Preview */
          <View style={styles.previewContainer}>
            <Image source={{ uri: capturedUri }} style={styles.previewImage} />
            <View style={styles.previewActions}>
              <TouchableOpacity
                style={styles.retakeButton}
                onPress={() => setCapturedUri(null)}
              >
                <Text style={styles.retakeButtonText}>Tirar Outra</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.confirmActionButton}
                onPress={() => {
                  onPhotoConfirmed(capturedUri);
                  setCapturedUri(null);
                  onClose();
                }}
              >
                <Text style={styles.confirmActionText}>Usar Foto</Text>
              </TouchableOpacity>
            </View>
          </View>
        ) : (
          /* Viewfinder */
          <CameraView
            ref={cameraRef}
            style={styles.camera}
            facing={facing}
            flash={flash}
          >
            <SafeAreaView style={styles.controlsOverlay}>
              {/* Top Controls: Flash, Close, Flip */}
              <View style={styles.topBar}>
                <TouchableOpacity style={styles.iconButton} onPress={onClose}>
                  <Text style={styles.iconText}>✕</Text>
                </TouchableOpacity>

                <TouchableOpacity style={styles.iconButton} onPress={cycleFlash}>
                  <Text style={styles.iconText}>
                    ⚡ {flash.toUpperCase()}
                  </Text>
                </TouchableOpacity>

                <TouchableOpacity
                  style={styles.iconButton}
                  onPress={toggleCameraFacing}
                >
                  <Text style={styles.iconText}>🔄</Text>
                </TouchableOpacity>
              </View>

              {/* Bottom Controls */}
              <View style={styles.bottomControls}>
                {/* Session Reel */}
                {sessionCaptures.length > 0 && (
                  <View style={styles.sessionReel}>
                    <FlatList
                      horizontal
                      data={sessionCaptures}
                      keyExtractor={(uri) => uri}
                      renderItem={({ item }) => (
                        <TouchableOpacity onPress={() => setCapturedUri(item)}>
                          <Image source={{ uri: item }} style={styles.reelThumb} />
                        </TouchableOpacity>
                      )}
                    />
                  </View>
                )}

                {/* Shutter Button (Thumb Reachable) */}
                <View style={styles.shutterRow}>
                  <TouchableOpacity
                    style={styles.shutterButton}
                    onPress={handleCapture}
                  >
                    <View style={styles.shutterInner} />
                  </TouchableOpacity>
                </View>
              </View>
            </SafeAreaView>
          </CameraView>
        )}
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
  },
  camera: {
    flex: 1,
  },
  controlsOverlay: {
    flex: 1,
    justifyContent: 'space-between',
    paddingHorizontal: 16,
  },
  topBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 12,
  },
  iconButton: {
    backgroundColor: 'rgba(0, 0, 0, 0.4)',
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: 20,
    minHeight: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  iconText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 14,
  },
  bottomControls: {
    paddingBottom: 24,
    alignItems: 'center',
  },
  sessionReel: {
    height: 60,
    width: '100%',
    marginBottom: 16,
  },
  reelThumb: {
    width: 52,
    height: 52,
    borderRadius: 8,
    marginRight: 8,
    borderWidth: 2,
    borderColor: '#FFFFFF',
  },
  shutterRow: {
    width: '100%',
    alignItems: 'center',
  },
  shutterButton: {
    width: 76,
    height: 76,
    borderRadius: 38,
    borderWidth: 4,
    borderColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
  },
  shutterInner: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#FFFFFF',
  },
  previewContainer: {
    flex: 1,
  },
  previewImage: {
    flex: 1,
    resizeMode: 'cover',
  },
  previewActions: {
    position: 'absolute',
    bottom: 40,
    left: 20,
    right: 20,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  retakeButton: {
    flex: 1,
    marginRight: 10,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    minHeight: 48,
    justifyContent: 'center',
  },
  retakeButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
    fontSize: 16,
  },
  confirmActionButton: {
    flex: 1,
    marginLeft: 10,
    backgroundColor: '#1E3A8A',
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    minHeight: 48,
    justifyContent: 'center',
  },
  confirmActionText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 16,
  },
  permissionContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
    backgroundColor: '#FFFFFF',
  },
  permissionTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 12,
  },
  permissionSubtitle: {
    fontSize: 15,
    color: '#4B5563',
    textAlign: 'center',
    marginBottom: 24,
    lineHeight: 22,
  },
  confirmButton: {
    backgroundColor: '#1E3A8A',
    paddingHorizontal: 28,
    paddingVertical: 14,
    borderRadius: 12,
    minHeight: 48,
    justifyContent: 'center',
  },
  confirmButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
    fontSize: 16,
  },
  cancelLink: {
    marginTop: 16,
    padding: 8,
  },
  cancelLinkText: {
    color: '#6B7280',
    fontSize: 15,
  },
});
