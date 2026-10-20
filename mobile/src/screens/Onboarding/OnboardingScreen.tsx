import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  TextInput,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import * as Location from 'expo-location';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface OnboardingScreenProps {
  onFinish: () => void;
}

export const OnboardingScreen: React.FC<OnboardingScreenProps> = ({ onFinish }) => {
  const [currentStep, setCurrentStep] = useState(0);
  const [citizenName, setCitizenName] = useState('');
  const [neighborhood, setNeighborhood] = useState('');

  const completeOnboarding = async () => {
    try {
      await AsyncStorage.setItem('has_completed_onboarding', 'true');
    } catch {}
    onFinish();
  };

  const requestGpsContextual = async () => {
    try {
      await Location.requestForegroundPermissionsAsync();
    } catch {}
    setCurrentStep(2);
  };

  const handleNext = () => {
    if (currentStep === 0) {
      setCurrentStep(1);
    } else if (currentStep === 1) {
      requestGpsContextual();
    } else {
      completeOnboarding();
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        style={styles.container}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      >
        {/* Top Header with Skip Option */}
        <View style={styles.topBar}>
          <View style={styles.progressRow}>
            {[0, 1, 2].map((idx) => (
              <View
                key={idx}
                style={[
                  styles.dot,
                  idx === currentStep ? styles.dotActive : styles.dotInactive,
                ]}
              />
            ))}
          </View>
          <TouchableOpacity
            style={styles.skipButton}
            onPress={completeOnboarding}
          >
            <Text style={styles.skipText}>Pular</Text>
          </TouchableOpacity>
        </View>

        {/* Step Content */}
        <View style={styles.contentContainer}>
          {currentStep === 0 && (
            <View style={styles.stepBox}>
              <Text style={styles.stepIcon}>📢</Text>
              <Text style={styles.stepTitle}>Sua voz na zeladoria da cidade</Text>
              <Text style={styles.stepDescription}>
                Reporte buracos, iluminação apagada, descarte irregular de lixo e
                muito mais diretamente para as secretarias municipais.
              </Text>
            </View>
          )}

          {currentStep === 1 && (
            <View style={styles.stepBox}>
              <Text style={styles.stepIcon}>📍</Text>
              <Text style={styles.stepTitle}>Localização exata no mapa</Text>
              <Text style={styles.stepDescription}>
                Utilizamos o seu GPS apenas para apontar onde o problema urbano
                está localizado, permitindo que a equipe de rua chegue ao local
                correto com precisão.
              </Text>
            </View>
          )}

          {currentStep === 2 && (
            <View style={styles.stepBox}>
              <Text style={styles.stepIcon}>👤</Text>
              <Text style={styles.stepTitle}>Como prefere ser chamado?</Text>
              <Text style={styles.stepDescription}>
                Personalize sua experiência para receber avisos sobre sua região.
              </Text>

              <TextInput
                style={styles.input}
                placeholder="Seu nome (opcional)"
                placeholderTextColor="#9CA3AF"
                value={citizenName}
                onChangeText={setCitizenName}
                returnKeyType="next"
              />

              <TextInput
                style={styles.input}
                placeholder="Seu bairro ou região (opcional)"
                placeholderTextColor="#9CA3AF"
                value={neighborhood}
                onChangeText={setNeighborhood}
                returnKeyType="done"
              />
            </View>
          )}
        </View>

        {/* Navigation Action */}
        <View style={styles.bottomBar}>
          <TouchableOpacity style={styles.actionButton} onPress={handleNext}>
            <Text style={styles.actionButtonText}>
              {currentStep === 2 ? 'Começar a Usar' : 'Continuar'}
            </Text>
          </TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  container: {
    flex: 1,
    justifyContent: 'space-between',
    paddingHorizontal: 24,
    paddingVertical: 16,
  },
  topBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    height: 48,
  },
  progressRow: {
    flexDirection: 'row',
    gap: 8,
  },
  dot: {
    height: 8,
    borderRadius: 4,
  },
  dotActive: {
    width: 24,
    backgroundColor: '#1E3A8A',
  },
  dotInactive: {
    width: 8,
    backgroundColor: '#E5E7EB',
  },
  skipButton: {
    minHeight: 44,
    minWidth: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  skipText: {
    fontSize: 15,
    color: '#6B7280',
    fontWeight: '600',
  },
  contentContainer: {
    flex: 1,
    justifyContent: 'center',
  },
  stepBox: {
    alignItems: 'center',
  },
  stepIcon: {
    fontSize: 64,
    marginBottom: 20,
  },
  stepTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#111827',
    textAlign: 'center',
    marginBottom: 12,
  },
  stepDescription: {
    fontSize: 15,
    color: '#4B5563',
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 24,
  },
  input: {
    width: '100%',
    height: 52,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 12,
    paddingHorizontal: 16,
    fontSize: 16,
    color: '#111827',
    marginBottom: 14,
    backgroundColor: '#F9FAFB',
  },
  bottomBar: {
    paddingBottom: 16,
  },
  actionButton: {
    height: 52,
    backgroundColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  actionButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
});
