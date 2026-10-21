import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
} from 'react-native';
import { useAuth } from '../../context/AuthContext';
import { getApiErrorMessage } from '../../api/client';
import { PasswordStrengthMeter } from '../../components/PasswordStrengthMeter';

interface SignUpScreenProps {
  onNavigateToLogin: () => void;
  onSuccessWelcome: () => void;
  onManagerAccess: () => void;
}

export const SignUpScreen: React.FC<SignUpScreenProps> = ({
  onNavigateToLogin,
  onSuccessWelcome,
  onManagerAccess,
}) => {
  const { registerWithEmail, enterAsGuest } = useAuth();

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [phone, setPhone] = useState('');
  const [termsAccepted, setTermsAccepted] = useState(false);
  const [marketingOptIn, setMarketingOptIn] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [isRegistered, setIsRegistered] = useState(false);

  const isFormValid =
    name.trim().length >= 3 &&
    email.includes('@') &&
    password.length >= 8 &&
    termsAccepted;

  const handleSubmit = async () => {
    if (!isFormValid) return;
    setIsLoading(true);
    setErrorMessage(null);
    try {
      await registerWithEmail({
        name,
        email,
        password,
        phone: phone || undefined,
        terms_accepted: termsAccepted,
        marketing_opt_in: marketingOptIn,
      });
      setIsRegistered(true);
    } catch (err: unknown) {
      setErrorMessage(
        getApiErrorMessage(err) ?? 'Erro ao criar conta. Verifique os dados e tente novamente.'
      );
    } finally {
      setIsLoading(false);
    }
  };

  // Welcome state immediately after account creation
  if (isRegistered) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <View style={styles.welcomeContainer}>
          <Text style={styles.welcomeIcon}>🎉</Text>
          <Text style={styles.welcomeTitle}>Conta criada com sucesso!</Text>
          <Text style={styles.welcomeSubtitle}>
            Bem-vindo à Central Inteligente de Ocorrências Públicas. Seu canal
            direto para cuidar da cidade.
          </Text>
          <TouchableOpacity
            style={styles.welcomeActionButton}
            onPress={onSuccessWelcome}
          >
            <Text style={styles.welcomeActionText}>Explorar o Mapa</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        style={styles.keyboardContainer}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          keyboardShouldPersistTaps="handled"
        >
          <Text style={styles.screenHeading}>Crie sua conta</Text>
          <Text style={styles.screenSubheading}>
            Acompanhe o andamento das suas solicitações em tempo real.
          </Text>

          {errorMessage && (
            <View style={styles.errorBox}>
              <Text style={styles.errorText}>{errorMessage}</Text>
            </View>
          )}

          {/* Form inputs */}
          <View style={styles.formGroup}>
            <Text style={styles.label}>Nome completo</Text>
            <TextInput
              style={styles.input}
              placeholder="Ex: João da Silva"
              placeholderTextColor="#9CA3AF"
              value={name}
              onChangeText={setName}
            />
          </View>

          <View style={styles.formGroup}>
            <Text style={styles.label}>E-mail</Text>
            <TextInput
              style={styles.input}
              placeholder="seuemail@exemplo.com"
              placeholderTextColor="#9CA3AF"
              keyboardType="email-address"
              autoCapitalize="none"
              value={email}
              onChangeText={setEmail}
            />
          </View>

          <View style={styles.formGroup}>
            <Text style={styles.label}>Telefone (opcional)</Text>
            <TextInput
              style={styles.input}
              placeholder="(71) 98888-8888"
              placeholderTextColor="#9CA3AF"
              keyboardType="phone-pad"
              value={phone}
              onChangeText={setPhone}
            />
          </View>

          <View style={styles.formGroup}>
            <Text style={styles.label}>Senha</Text>
            <TextInput
              style={styles.input}
              placeholder="Mínimo 8 caracteres"
              placeholderTextColor="#9CA3AF"
              secureTextEntry
              value={password}
              onChangeText={setPassword}
            />
            {/* Inline password strength feedback */}
            <PasswordStrengthMeter password={password} />
          </View>

          {/* Terms & LGPD Privacy Policy Checkbox */}
          <TouchableOpacity
            style={styles.checkboxRow}
            onPress={() => setTermsAccepted(!termsAccepted)}
          >
            <View
              style={[
                styles.checkboxBox,
                termsAccepted && styles.checkboxBoxChecked,
              ]}
            >
              {termsAccepted && <Text style={styles.checkmark}>✓</Text>}
            </View>
            <Text style={styles.checkboxText}>
              Li e concordo com os{' '}
              <Text style={styles.linkText}>Termos de Uso</Text> e{' '}
              <Text style={styles.linkText}>Política de Privacidade (LGPD)</Text>
              .*
            </Text>
          </TouchableOpacity>

          {/* Optional Marketing Opt-In */}
          <TouchableOpacity
            style={styles.checkboxRow}
            onPress={() => setMarketingOptIn(!marketingOptIn)}
          >
            <View
              style={[
                styles.checkboxBox,
                marketingOptIn && styles.checkboxBoxChecked,
              ]}
            >
              {marketingOptIn && <Text style={styles.checkmark}>✓</Text>}
            </View>
            <Text style={styles.checkboxText}>
              Aceito receber novidades e informativos sobre melhorias no
              município.
            </Text>
          </TouchableOpacity>

          {/* Submit Button */}
          <TouchableOpacity
            style={[
              styles.submitButton,
              (!isFormValid || isLoading) && styles.submitButtonDisabled,
            ]}
            disabled={!isFormValid || isLoading}
            onPress={handleSubmit}
          >
            {isLoading ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <Text style={styles.submitButtonText}>Criar Conta</Text>
            )}
          </TouchableOpacity>

          {/* Already have an account */}
          <TouchableOpacity
            style={styles.loginLink}
            onPress={onNavigateToLogin}
          >
            <Text style={styles.loginLinkText}>
              Já tem uma conta? <Text style={styles.boldLink}>Conectar-se</Text>
            </Text>
          </TouchableOpacity>

          {/* Sem cadastro: mapa público + ocorrência anônima acompanhada por protocolo */}
          <TouchableOpacity style={styles.guestButton} onPress={enterAsGuest}>
            <Text style={styles.guestButtonText}>Registrar sem cadastro (anônimo)</Text>
          </TouchableOpacity>

          <TouchableOpacity style={styles.loginLink} onPress={onManagerAccess}>
            <Text style={styles.loginLinkText}>
              É gestor de um órgão? <Text style={styles.boldLink}>Acesso do gestor</Text>
            </Text>
          </TouchableOpacity>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  keyboardContainer: {
    flex: 1,
  },
  scrollContent: {
    padding: 24,
  },
  screenHeading: {
    fontSize: 26,
    fontWeight: '800',
    color: '#111827',
  },
  screenSubheading: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 6,
    marginBottom: 20,
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
  checkboxRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 14,
    paddingRight: 16,
  },
  checkboxBox: {
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
  checkboxBoxChecked: {
    backgroundColor: '#1E3A8A',
    borderColor: '#1E3A8A',
  },
  checkmark: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '800',
  },
  checkboxText: {
    fontSize: 13,
    color: '#4B5563',
    lineHeight: 18,
    flex: 1,
  },
  linkText: {
    color: '#1E3A8A',
    textDecorationLine: 'underline',
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
  guestButton: {
    minHeight: 48,
    borderWidth: 1.5,
    borderColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 16,
  },
  guestButtonText: {
    color: '#1E3A8A',
    fontSize: 15,
    fontWeight: '700',
  },
  loginLink: {
    marginTop: 20,
    alignItems: 'center',
    padding: 8,
  },
  loginLinkText: {
    fontSize: 14,
    color: '#6B7280',
  },
  boldLink: {
    color: '#1E3A8A',
    fontWeight: '700',
  },
  welcomeContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  welcomeIcon: {
    fontSize: 64,
    marginBottom: 20,
  },
  welcomeTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: '#111827',
    textAlign: 'center',
    marginBottom: 12,
  },
  welcomeSubtitle: {
    fontSize: 15,
    color: '#4B5563',
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 32,
  },
  welcomeActionButton: {
    width: '100%',
    height: 52,
    backgroundColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  welcomeActionText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
});
