import React, { useEffect, useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Animated,
  ActivityIndicator,
} from 'react-native';

interface SplashScreenProps {
  isReady: boolean;
  onAnimationComplete: () => void;
}

export const SplashScreen: React.FC<SplashScreenProps> = ({
  isReady,
  onAnimationComplete,
}) => {
  const fadeAnim = useRef(new Animated.Value(1)).current;
  const [showSpinner, setShowSpinner] = useState(false);

  useEffect(() => {
    // Se a inicialização demorar mais de 1 segundo, exibe indicador sutil
    const timer = setTimeout(() => {
      setShowSpinner(true);
    }, 1000);

    return () => clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (isReady) {
      // Transição suave via fade-out (sem corte brusco)
      Animated.timing(fadeAnim, {
        toValue: 0,
        duration: 350,
        useNativeDriver: true,
      }).start(() => {
        onAnimationComplete();
      });
    }
  }, [isReady, fadeAnim, onAnimationComplete]);

  return (
    <Animated.View style={[styles.container, { opacity: fadeAnim }]}>
      <View style={styles.brandBox}>
        <Text style={styles.logoIcon}>🏛️</Text>
        <Text style={styles.appName}>CIOP</Text>
        <Text style={styles.appTagline}>Central Inteligente de Ocorrências Públicas</Text>
      </View>

      {showSpinner && (
        <View style={styles.spinnerContainer}>
          <ActivityIndicator size="small" color="#93C5FD" />
        </View>
      )}
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  container: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: '#1E3A8A',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 9999,
  },
  brandBox: {
    alignItems: 'center',
  },
  logoIcon: {
    fontSize: 56,
    marginBottom: 12,
  },
  appName: {
    fontSize: 32,
    fontWeight: '800',
    color: '#FFFFFF',
    letterSpacing: 2,
  },
  appTagline: {
    fontSize: 14,
    color: '#BFDBFE',
    marginTop: 8,
    textAlign: 'center',
  },
  spinnerContainer: {
    position: 'absolute',
    bottom: 60,
  },
});
