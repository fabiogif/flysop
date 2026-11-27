import { SurveyQuestion } from '../../src/types';

describe('Validador de Perguntas e Respostas de Pesquisas (Surveys)', () => {
  const sampleQuestions: SurveyQuestion[] = [
    {
      id: 1,
      type: 'text',
      prompt: 'Descreva a qualidade da pavimentação na sua rua',
      required: true,
      sort_order: 1,
    },
    {
      id: 2,
      type: 'single_choice',
      prompt: 'Você utiliza o transporte público diariamente?',
      options: ['Sim', 'Não', 'Às vezes'],
      required: true,
      sort_order: 2,
    },
    {
      id: 3,
      type: 'scale',
      prompt: 'De 1 a 5, qual a sua nota para a iluminação pública?',
      required: true,
      sort_order: 3,
    },
    {
      id: 4,
      type: 'text',
      prompt: 'Comentários adicionais (opcional)',
      required: false,
      sort_order: 4,
    },
  ];

  const validateSurveyAnswers = (
    questions: SurveyQuestion[],
    answers: Record<number, string | number>
  ): { isValid: boolean; missingQuestionIds: number[] } => {
    const missing: number[] = [];

    for (const q of questions) {
      if (q.required) {
        const val = answers[q.id];
        if (val === undefined || val === null || val === '') {
          missing.push(q.id);
        }
      }
    }

    return {
      isValid: missing.length === 0,
      missingQuestionIds: missing,
    };
  };

  it('valida com sucesso quando todas as perguntas obrigatórias são respondidas', () => {
    const validAnswers = {
      1: 'Asfalto regular com alguns buracos',
      2: 'Sim',
      3: 4, // nota 4 de 1 a 5
    };

    const result = validateSurveyAnswers(sampleQuestions, validAnswers);
    expect(result.isValid).toBe(true);
    expect(result.missingQuestionIds).toHaveLength(0);
  });

  it('detecta perguntas obrigatórias pendentes quando não respondidas', () => {
    const incompleteAnswers = {
      1: 'Asfalto regular',
      // Questão 2 e 3 omitidas
    };

    const result = validateSurveyAnswers(sampleQuestions, incompleteAnswers);
    expect(result.isValid).toBe(false);
    expect(result.missingQuestionIds).toEqual([2, 3]);
  });

  it('permite que perguntas opcionais fiquem em branco', () => {
    const answersWithoutOptional = {
      1: 'Texto obrigatório preenchido',
      2: 'Não',
      3: 5,
      // Questão 4 é opcional (required: false) e não preenchida
    };

    const result = validateSurveyAnswers(sampleQuestions, answersWithoutOptional);
    expect(result.isValid).toBe(true);
  });

  it('valida que respostas de escala estão estritamente no intervalo fechado de 1 a 5', () => {
    const isScaleValueValid = (val: number) => Number.isInteger(val) && val >= 1 && val <= 5;

    expect(isScaleValueValid(1)).toBe(true);
    expect(isScaleValueValid(3)).toBe(true);
    expect(isScaleValueValid(5)).toBe(true);
    expect(isScaleValueValid(0)).toBe(false);
    expect(isScaleValueValid(6)).toBe(false);
    expect(isScaleValueValid(3.5)).toBe(false);
  });
});
