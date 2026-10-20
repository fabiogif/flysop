import React, { useState, useEffect } from 'react';
import { StyleSheet } from 'react-native';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { NavigationContainer } from '@react-navigation/native';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthProvider, useAuth } from './src/context/AuthContext';
import { SplashScreen } from './src/screens/Splash/SplashScreen';
import { OnboardingScreen } from './src/screens/Onboarding/OnboardingScreen';
import { SignUpScreen } from './src/screens/Auth/SignUpScreen';
import { ManagerLoginScreen } from './src/screens/Auth/ManagerLoginScreen';
import { TabNavigator } from './src/navigation/TabNavigator';
import { ManagerNavigator } from './src/navigation/ManagerNavigator';
import { OfflineQueueService } from './src/services/offlineQueue';

const queryClient = new QueryClient();


const RootNavigation: React.FC = () => {
  const { isAuthenticated, isGuest, manager, isLoading: isAuthLoading } = useAuth();
  const [hasOnboarding, setHasOnboarding] = useState<boolean | null>(null);
  const [isSplashDone, setIsSplashDone] = useState(false);
  const [showSignUp, setShowSignUp] = useState(true);
  const [showManagerLogin, setShowManagerLogin] = useState(false);

  useEffect(() => {
    const unsubscribeSync = OfflineQueueService.setupAutoSyncListener();
    (async () => {
      try {
        const val = await AsyncStorage.getItem('has_completed_onboarding');
        setHasOnboarding(val === 'true');
      } catch {
        setHasOnboarding(false);
      }
    })();
    return () => unsubscribeSync();
  }, []);
  const isReady = !isAuthLoading && hasOnboarding !== null;
  const isAnonymous = !isAuthenticated && !isGuest;

  const renderContent = () => {
    if (!hasOnboarding) return <OnboardingScreen onFinish={() => setHasOnboarding(true)} />;
    // Gestor de órgão tem navegação própria (ocorrências do órgão + notificações)
    if (manager) return <ManagerNavigator />;
    if (isAnonymous && showManagerLogin) {
      return <ManagerLoginScreen onBack={() => setShowManagerLogin(false)} />;
    }
    if (isAnonymous && showSignUp) {
      return (
        <SignUpScreen
          onNavigateToLogin={() => setShowSignUp(false)}
          onSuccessWelcome={() => setShowSignUp(false)}
          onManagerAccess={() => setShowManagerLogin(true)}
        />
      );
    }
    return <TabNavigator />;
  };

  return (
    <GestureHandlerRootView style={styles.container}>
      {!isSplashDone && (
        <SplashScreen
          isReady={isReady}
          onAnimationComplete={() => setIsSplashDone(true)}
        />
      )}

      {isReady && isSplashDone && <NavigationContainer>{renderContent()}</NavigationContainer>}
    </GestureHandlerRootView>
  );
};

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <RootNavigation />
      </AuthProvider>
    </QueryClientProvider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
});
