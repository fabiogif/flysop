import React, { useState, useEffect } from 'react';
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
import { apiClient } from '../../api/client';
import { saveAnsweredSurvey } from '../../services/answeredSurveys';
import { Survey, SurveyQuestion } from '../../types';

interface SurveyDetailScreenProps {
  token: string;
  onFinish: () => void;
}

export const SurveyDetailScreen: React.FC<SurveyDetailScreenProps> = ({
  token,
  onFinish,
}) => {
  const [survey, setSurvey] = useState<Survey | null>(null);
  const [answers, setAnswers] = useState<Record<number, string | number>>({});
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isCompleted, setIsCompleted] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  useEffect(() => {
    (async () => {
      try {
        const response = await apiClient.get<{ data?: Survey } & Survey>(
          `/surveys/${token}`
        );
        const data = response.data.data ?? response.data;
        setSurvey(data);
      } catch {
        setErrorMessage('Não foi possível carregar a pesquisa.');
      } finally {
        setIsLoading(false);
      }
    })();
  }, [token]);

  const handleTextAnswer = (questionId: number, value: string) => {
    setAnswers((prev) => ({ ...prev, [questionId]: value }));
  };

  const handleChoiceAnswer = (questionId: number, value: string) => {
    setAnswers((prev) => ({ ...prev, [questionId]: value }));
  };

  const handleScaleAnswer = (questionId: number, value: number) => {
    setAnswers((prev) => ({ ...prev, [questionId]: value }));
  };

  const isFormValid = () => {
    if (!survey?.questions) return false;
    for (const q of survey.questions) {
      if (q.required) {
        const val = answers[q.id];
        if (val === undefined || val === null || val === '') {
          return false;
        }
      }
    }
    return true;
  };

  const handleSubmit = async () => {
    if (!isFormValid()) return;
    setIsSubmitting(true);
    setErrorMessage(null);

    try {
      await apiClient.post(`/surveys/${token}/responses`, {
        answers,
      });
      await saveAnsweredSurvey(token).catch(() => {});
      setIsCompleted(true);
    } catch (err: unknown) {
      let msg: string | null = null;
      if (err && typeof err === 'object' && 'response' in err) {
        const res = err.response;
        if (res && typeof res === 'object' && 'data' in res) {
          const data = res.data;
          if (data && typeof data === 'object' && 'message' in data && typeof data.message === 'string') {
            msg = data.message;
          }
        }
      }
      setErrorMessage(msg ?? 'Erro ao enviar respostas. Tente novamente.');
    } finally {
      setIsSubmitting(false);
    }
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#1E3A8A" />
      </SafeAreaView>
    );
  }

  if (isCompleted) {
    return (
      <SafeAreaView style={styles.centerContainer}>
        <View style={styles.completedBox}>
          <Text style={styles.completedIcon}>🎉</Text>
          <Text style={styles.completedTitle}>Obrigado pela sua resposta!</Text>
          <Text style={styles.completedSubtitle}>
            Sua opinião é fundamental para o aprimoramento dos serviços públicos
            do nosso município.
          </Text>
          <TouchableOpacity style={styles.finishBtn} onPress={onFinish}>
            <Text style={styles.finishBtnText}>Voltar às Pesquisas</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  if (!survey) {
    return (
      <SafeAreaView style={styles.centerContainer}>
        <Text style={styles.errorText}>Pesquisa não encontrada.</Text>
        <TouchableOpacity style={styles.backButton} onPress={onFinish}>
          <Text style={styles.backButtonText}>Voltar às Pesquisas</Text>
        </TouchableOpacity>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView contentContainerStyle={styles.container}>
        <Text style={styles.surveyTitle}>{survey.title}</Text>
        {survey.description ? (
          <Text style={styles.surveyDescription}>{survey.description}</Text>
        ) : null}

        {errorMessage && (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{errorMessage}</Text>
          </View>
        )}

        {/* Dynamic Questions Renderer */}
        <View style={styles.questionsList}>
          {survey.questions?.map((q, idx) => (
            <View key={q.id} style={styles.questionCard}>
              <Text style={styles.questionPrompt}>
                {idx + 1}. {q.prompt}{' '}
                {q.required && <Text style={styles.requiredStar}>*</Text>}
              </Text>

              {/* Type: text */}
              {q.type === 'text' && (
                <TextInput
                  style={styles.textInput}
                  placeholder="Escreva sua resposta..."
                  value={String(answers[q.id] || '')}
                  onChangeText={(val) => handleTextAnswer(q.id, val)}
                  multiline
                  numberOfLines={3}
                />
              )}

              {/* Type: single_choice */}
              {q.type === 'single_choice' && (
                <View style={styles.choiceGroup}>
                  {q.options?.map((opt) => {
                    const isSelected = answers[q.id] === opt;
                    return (
                      <TouchableOpacity
                        key={opt}
                        style={[
                          styles.choiceOption,
                          isSelected && styles.choiceOptionSelected,
                        ]}
                        onPress={() => handleChoiceAnswer(q.id, opt)}
                      >
                        <View
                          style={[
                            styles.radioCircle,
                            isSelected && styles.radioCircleSelected,
                          ]}
                        />
                        <Text
                          style={[
                            styles.choiceLabel,
                            isSelected && styles.choiceLabelSelected,
                          ]}
                        >
                          {opt}
                        </Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>
              )}

              {/* Type: scale (1 to 5) */}
              {q.type === 'scale' && (
                <View style={styles.scaleContainer}>
                  {[1, 2, 3, 4, 5].map((num) => {
                    const isSelected = answers[q.id] === num;
                    return (
                      <TouchableOpacity
                        key={num}
                        style={[
                          styles.scaleButton,
                          isSelected && styles.scaleButtonSelected,
                        ]}
                        onPress={() => handleScaleAnswer(q.id, num)}
                      >
                        <Text
                          style={[
                            styles.scaleNumber,
                            isSelected && styles.scaleNumberSelected,
                          ]}
                        >
                          {num}
                        </Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>
              )}
            </View>
          ))}
        </View>

        {/* Action Button */}
        <TouchableOpacity
          style={[
            styles.submitButton,
            (!isFormValid() || isSubmitting) && styles.submitButtonDisabled,
          ]}
          disabled={!isFormValid() || isSubmitting}
          onPress={handleSubmit}
        >
          {isSubmitting ? (
            <ActivityIndicator color="#FFFFFF" />
          ) : (
            <Text style={styles.submitButtonText}>Enviar Respostas</Text>
          )}
        </TouchableOpacity>

        <TouchableOpacity style={styles.backButton} onPress={onFinish} disabled={isSubmitting}>
          <Text style={styles.backButtonText}>Voltar às Pesquisas</Text>
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
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
    backgroundColor: '#FFFFFF',
  },
  container: {
    padding: 20,
    paddingBottom: 40,
  },
  surveyTitle: {
    fontSize: 22,
    fontWeight: '800',
    color: '#111827',
  },
  surveyDescription: {
    fontSize: 14,
    color: '#4B5563',
    marginTop: 6,
    lineHeight: 20,
    marginBottom: 20,
  },
  errorBox: {
    backgroundColor: '#FEE2E2',
    padding: 12,
    borderRadius: 8,
    marginBottom: 16,
  },
  errorText: {
    color: '#DC2626',
    fontSize: 13,
  },
  questionsList: {
    gap: 20,
  },
  questionCard: {
    backgroundColor: '#F9FAFB',
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  questionPrompt: {
    fontSize: 15,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: 12,
    lineHeight: 22,
  },
  requiredStar: {
    color: '#DC2626',
  },
  textInput: {
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    color: '#111827',
    backgroundColor: '#FFFFFF',
    height: 80,
    textAlignVertical: 'top',
  },
  choiceGroup: {
    gap: 8,
  },
  choiceOption: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    backgroundColor: '#FFFFFF',
    minHeight: 44,
  },
  choiceOptionSelected: {
    borderColor: '#1E3A8A',
    backgroundColor: '#EFF6FF',
  },
  radioCircle: {
    width: 18,
    height: 18,
    borderRadius: 9,
    borderWidth: 2,
    borderColor: '#9CA3AF',
    marginRight: 10,
  },
  radioCircleSelected: {
    borderColor: '#1E3A8A',
    backgroundColor: '#1E3A8A',
  },
  choiceLabel: {
    fontSize: 14,
    color: '#374151',
    flex: 1,
  },
  choiceLabelSelected: {
    color: '#1E3A8A',
    fontWeight: '600',
  },
  scaleContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 6,
  },
  scaleButton: {
    width: 48,
    height: 48,
    borderRadius: 24,
    borderWidth: 1.5,
    borderColor: '#D1D5DB',
    backgroundColor: '#FFFFFF',
    justifyContent: 'center',
    alignItems: 'center',
  },
  scaleButtonSelected: {
    borderColor: '#1E3A8A',
    backgroundColor: '#1E3A8A',
  },
  scaleNumber: {
    fontSize: 16,
    fontWeight: '700',
    color: '#374151',
  },
  scaleNumberSelected: {
    color: '#FFFFFF',
  },
  submitButton: {
    height: 52,
    backgroundColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 28,
  },
  submitButtonDisabled: {
    backgroundColor: '#93C5FD',
  },
  submitButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
  // Secundário (contorno), abaixo do "Enviar Respostas"
  backButton: {
    alignSelf: 'stretch',
    height: 52,
    borderWidth: 1.5,
    borderColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 12,
  },
  backButtonText: {
    color: '#1E3A8A',
    fontSize: 16,
    fontWeight: '700',
  },
  completedBox: {
    alignItems: 'center',
    width: '100%',
  },
  completedIcon: {
    fontSize: 64,
    marginBottom: 16,
  },
  completedTitle: {
    fontSize: 22,
    fontWeight: '800',
    color: '#111827',
    textAlign: 'center',
    marginBottom: 8,
  },
  completedSubtitle: {
    fontSize: 15,
    color: '#4B5563',
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 28,
  },
  finishBtn: {
    width: '100%',
    height: 50,
    backgroundColor: '#1E3A8A',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  finishBtnText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
});
