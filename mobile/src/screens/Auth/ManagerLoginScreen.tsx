import React from 'react';
import { useAuth } from '../../context/AuthContext';
import { LoginScreen } from './LoginScreen';

interface ManagerLoginScreenProps {
  onBack: () => void;
}

// Gestor de órgão entra com as mesmas credenciais do painel web
export const ManagerLoginScreen: React.FC<ManagerLoginScreenProps> = ({ onBack }) => {
  const { loginAsManager } = useAuth();

  return (
    <LoginScreen
      heading="Acesso do gestor"
      subheading="Entre com seu usuário do painel para analisar as ocorrências do seu órgão."
      emailPlaceholder="seuemail@prefeitura.gov.br"
      passwordPlaceholder="Sua senha do painel"
      onSubmit={loginAsManager}
      onBack={onBack}
    />
  );
};
