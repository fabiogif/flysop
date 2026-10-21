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
import { LoginScreen, LoginFooterLink } from './src/screens/Auth/LoginScreen';
import { TabNavigator } from './src/navigation/TabNavigator';
import { ManagerNavigator } from './src/navigation/ManagerNavigator';
import { OfflineQueueService } from './src/services/offlineQueue';

const queryClient = new QueryClient();


const RootNavigation: React.FC = () => {
  const { isAuthenticated, isGuest, manager, isLoading: isAuthLoading, loginWithEmail, enterAsGuest } = useAuth();
  const [hasOnboarding, setHasOnboarding] = useState<boolean | null>(null);
  const [isSplashDone, setIsSplashDone] = useState(false);
  // Tela de acesso de quem ainda não entrou (nem com conta, nem sem cadastro)
  const [authScreen, setAuthScreen] = useState<'signup' | 'login' | 'manager'>('signup');

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
    // As abas só aparecem com conta ou no modo sem cadastro: antes, "Conectar-se" escondia o
    // cadastro e abria as abas sem login nem modo anônimo (ocorrência enviada sem guardar o código)
    if (isAnonymous) {
      if (authScreen === 'manager') {
        return <ManagerLoginScreen onBack={() => setAuthScreen('signup')} />;
      }
      if (authScreen === 'login') {
        return (
          <LoginScreen
            heading="Entrar"
            subheading="Acesse sua conta para acompanhar suas ocorrências em qualquer aparelho."
            emailPlaceholder="seuemail@exemplo.com"
            passwordPlaceholder="Sua senha"
            onSubmit={loginWithEmail}
            onBack={() => setAuthScreen('signup')}
            footer={
              <>
                <LoginFooterLink text="Não tem conta?" action="Criar conta" onPress={() => setAuthScreen('signup')} />
                <LoginFooterLink text="Prefere não se cadastrar?" action="Registrar sem cadastro" onPress={enterAsGuest} />
              </>
            }
          />
        );
      }
      return (
        <SignUpScreen
          onNavigateToLogin={() => setAuthScreen('login')}
          onSuccessWelcome={() => setAuthScreen('signup')}
          onManagerAccess={() => setAuthScreen('manager')}
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
