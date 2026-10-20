import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

interface PasswordStrengthMeterProps {
  password: string;
}

export const PasswordStrengthMeter: React.FC<PasswordStrengthMeterProps> = ({ password }) => {
  const hasMinLength = password.length >= 8;
  const hasLetter = /[a-zA-Z]/.test(password);
  const hasNumber = /[0-9]/.test(password);

  const score = [hasMinLength, hasLetter, hasNumber].filter(Boolean).length;

  const strengthLabels = ['Muito Fraca', 'Fraca', 'Média', 'Forte'];
  const strengthColors = ['#E5E7EB', '#EF4444', '#F59E0B', '#10B981'];

  return (
    <View style={styles.container}>
      <View style={styles.barContainer}>
        {[1, 2, 3].map((step) => (
          <View
            key={step}
            style={[
              styles.bar,
              {
                backgroundColor:
                  score >= step ? strengthColors[score] : '#E5E7EB',
              },
            ]}
          />
        ))}
      </View>
      {password.length > 0 && (
        <Text style={[styles.statusText, { color: strengthColors[score] }]}>
          {strengthLabels[score]}
        </Text>
      )}
      <View style={styles.requirementsContainer}>
        <Text style={[styles.requirement, hasMinLength && styles.requirementMet]}>
          • Pelo menos 8 caracteres
        </Text>
        <Text style={[styles.requirement, hasLetter && styles.requirementMet]}>
          • Contém pelo menos uma letra
        </Text>
        <Text style={[styles.requirement, hasNumber && styles.requirementMet]}>
          • Contém pelo menos um número
        </Text>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginVertical: 8,
  },
  barContainer: {
    flexDirection: 'row',
    height: 4,
    gap: 6,
  },
  bar: {
    flex: 1,
    borderRadius: 2,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
    marginTop: 4,
  },
  requirementsContainer: {
    marginTop: 6,
  },
  requirement: {
    fontSize: 12,
    color: '#6B7280',
    lineHeight: 18,
  },
  requirementMet: {
    color: '#059669',
    fontWeight: '500',
  },
});
