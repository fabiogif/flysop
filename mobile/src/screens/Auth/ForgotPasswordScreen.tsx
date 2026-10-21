import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, SafeAreaView, ScrollView, ActivityIndicator } from 'react-native';
import { apiClient, getApiErrorMessage } from '../../api/client';
import { styles } from './LoginScreen';

// Pede o link de redefinição; a nova senha é criada na página aberta pelo e-mail
export const ForgotPasswordScreen: React.FC<{ onBack: () => void }> = ({ onBack }) => {
  const [email, setEmail] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [sentMessage, setSentMessage] = useState<string | null>(null);

  const isFormValid = email.includes('@');

  const handleSend = async () => {
    if (!isFormValid) return;
    setIsLoading(true);
    setErrorMessage(null);
    try {
      const response = await apiClient.post<{ message: string }>('/auth/forgot-password', { email: email.trim() });
      setSentMessage(response.data.message);
    } catch (err: unknown) {
      setErrorMessage(getApiErrorMessage(err) ?? 'Não foi possível enviar. Tente novamente.');
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

        <Text style={styles.heading}>Esqueci minha senha</Text>
        <Text style={styles.subheading}>
          Informe o e-mail da sua conta. Enviaremos um link para você criar uma nova senha.
        </Text>

        {errorMessage && (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{errorMessage}</Text>
          </View>
        )}

        {sentMessage ? (
          <Text style={styles.subheading}>{sentMessage} Depois, volte e entre com a nova senha.</Text>
        ) : (
          <>
            <View style={styles.formGroup}>
              <Text style={styles.label}>E-mail</Text>
              <TextInput
                style={styles.input}
                value={email}
                onChangeText={setEmail}
                placeholder="seuemail@exemplo.com"
                placeholderTextColor="#9CA3AF"
                keyboardType="email-address"
                autoCapitalize="none"
                autoCorrect={false}
                onSubmitEditing={handleSend}
                accessibilityLabel="E-mail"
              />
            </View>

            <TouchableOpacity
              style={[styles.submitButton, (!isFormValid || isLoading) && styles.submitButtonDisabled]}
              disabled={!isFormValid || isLoading}
              onPress={handleSend}
            >
              {isLoading ? (
                <ActivityIndicator color="#FFFFFF" />
              ) : (
                <Text style={styles.submitButtonText}>Enviar link</Text>
              )}
            </TouchableOpacity>
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};
