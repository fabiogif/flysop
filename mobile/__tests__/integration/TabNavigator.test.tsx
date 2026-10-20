import React from 'react';
import { render, fireEvent } from '@testing-library/react-native';
import { NavigationContainer } from '@react-navigation/native';
import * as Haptics from 'expo-haptics';
import { TabNavigator } from '../../src/navigation/TabNavigator';

jest.mock('expo-haptics', () => ({
  impactAsync: jest.fn(),
  ImpactFeedbackStyle: {
    Light: 'light',
  },
}));

jest.mock('../../src/screens/Occurrences/MapScreen', () => ({
  MapScreen: () => <></>,
}));

jest.mock('../../src/screens/Occurrences/MyOccurrencesScreen', () => ({
  MyOccurrencesScreen: () => <></>,
}));

jest.mock('../../src/screens/Occurrences/NewOccurrenceScreen', () => ({
  NewOccurrenceScreen: () => <></>,
}));

jest.mock('../../src/screens/Surveys/SurveyListScreen', () => ({
  SurveyListScreen: () => <></>,
}));

jest.mock('../../src/screens/Profile/EditProfileScreen', () => ({
  EditProfileScreen: () => <></>,
}));

describe('TabNavigator Integration', () => {
  it('renderiza as 5 abas principais do aplicativo', () => {
    const { getByText } = render(
      <NavigationContainer>
        <TabNavigator />
      </NavigationContainer>
    );

    expect(getByText('Mapa')).toBeTruthy();
    expect(getByText('Ocorrências')).toBeTruthy();
    expect(getByText('Nova')).toBeTruthy();
    expect(getByText('Pesquisas')).toBeTruthy();
    expect(getByText('Perfil')).toBeTruthy();
  });

  it('aciona retorno tátil (haptic feedback) ao trocar de aba', () => {
    const { getByText } = render(
      <NavigationContainer>
        <TabNavigator />
      </NavigationContainer>
    );

    fireEvent.press(getByText('Pesquisas'));
    expect(Haptics.impactAsync).toHaveBeenCalled();
  });
});
