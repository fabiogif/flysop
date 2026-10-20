import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
} from 'react-native';
import { useAuth } from '../../context/AuthContext';
import { getApiErrorMessage } from '../../api/client';

interface ManagerLoginScreenProps {
  onBack: () => void;
}

// Gestor de órgão entra com as mesmas credenciais do painel web
export const ManagerLoginScreen: React.FC<ManagerLoginScreenProps> = ({ onBack }) => {
  const { loginAsManager } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const isFormValid = email.includes('@') && password.length > 0;

  const handleLogin = async () => {
    if (!isFormValid) return;
    setIsLoading(true);
    setErrorMessage(null);
    try {
      await loginAsManager(email.trim(), password);
    } catch (err: unknown) {
      setErrorMessage(getApiErrorMessage(err) ?? 'Não foi possível entrar. Tente novamente.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.container} keyboardShouldPersistTaps="handled">
        <TouchableOpacity onPress={onBack} style={styles.backLink} accessibilityRole="button">
          <Text style={styles.backLinkText}>← Voltar</Text>
        </TouchableOpacity>

        <Text style={styles.heading}>Acesso do gestor</Text>
        <Text style={styles.subheading}>
          Entre com seu usuário do painel para analisar as ocorrências do seu órgão.
        </Text>

        {errorMessage && (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{errorMessage}</Text>
          </View>
        )}

        <View style={styles.formGroup}>
          <Text style={styles.label}>E-mail</Text>
          <TextInput
            style={styles.input}
            value={email}
            onChangeText={setEmail}
            placeholder="seuemail@prefeitura.gov.br"
            placeholderTextColor="#9CA3AF"
            keyboardType="email-address"
            autoCapitalize="none"
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Senha</Text>
          <TextInput
            style={styles.input}
            value={password}
            onChangeText={setPassword}
            placeholder="Sua senha do painel"
            placeholderTextColor="#9CA3AF"
            secureTextEntry
          />
        </View>

        <TouchableOpacity
          style={[styles.submitButton, (!isFormValid || isLoading) && styles.submitButtonDisabled]}
          disabled={!isFormValid || isLoading}
          onPress={handleLogin}
        >
          {isLoading ? (
            <ActivityIndicator color="#FFFFFF" />
          ) : (
            <Text style={styles.submitButtonText}>Entrar</Text>
          )}
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  container: {
    padding: 24,
  },
  backLink: {
    paddingVertical: 8,
    marginBottom: 12,
    alignSelf: 'flex-start',
  },
  backLinkText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1E3A8A',
  },
  heading: {
    fontSize: 26,
    fontWeight: '800',
    color: '#111827',
  },
  subheading: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 6,
    marginBottom: 24,
  },
  errorBox: {
    backgroundColor: '#FEE2E2',
    padding: 12,
    borderRadius: 8,
    marginBottom: 16,
  },
  errorText: {
    color: '#B91C1C',
    fontSize: 13,
  },
  formGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#374151',
    marginBottom: 6,
  },
  input: {
    height: 50,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 12,
    paddingHorizontal: 16,
    fontSize: 15,
    color: '#111827',
    backgroundColor: '#F9FAFB',
  },
  submitButton: {
    height: 52,
    backgroundColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 12,
  },
  submitButtonDisabled: {
    backgroundColor: '#93C5FD',
  },
  submitButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
});
