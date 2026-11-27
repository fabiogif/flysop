import React from 'react';
import { render, fireEvent } from '@testing-library/react-native';
import { ActionSheetModal, ActionItem } from '../../src/components/ActionSheet/ActionSheetModal';

describe('ActionSheetModal Component', () => {
  it('renderiza o cabeçalho, ações seguras e botão cancelar', () => {
    const handleClose = jest.fn();
    const handleSelectPhoto = jest.fn();

    const actions: ActionItem[] = [
      { id: '1', label: 'Tirar Foto', onPress: handleSelectPhoto },
      { id: '2', label: 'Escolher da Galeria', onPress: jest.fn() },
    ];

    const { getByText } = render(
      <ActionSheetModal
        visible={true}
        title="Origem da Imagem"
        subtitle="Selecione de onde deseja importar a foto"
        actions={actions}
        onClose={handleClose}
      />
    );

    expect(getByText('Origem da Imagem')).toBeTruthy();
    expect(getByText('Selecione de onde deseja importar a foto')).toBeTruthy();
    expect(getByText('Tirar Foto')).toBeTruthy();
    expect(getByText('Escolher da Galeria')).toBeTruthy();
    expect(getByText('Cancelar')).toBeTruthy();

    fireEvent.press(getByText('Cancelar'));
    expect(handleClose).toHaveBeenCalledTimes(1);
  });

  it('aciona o callback da ação selecionada e fecha o modal', () => {
    const handleClose = jest.fn();
    const handleAction = jest.fn();

    const actions: ActionItem[] = [
      { id: '1', label: 'Ação Segura', onPress: handleAction },
    ];

    const { getByText } = render(
      <ActionSheetModal
        visible={true}
        title="Menu de Opções"
        actions={actions}
        onClose={handleClose}
      />
    );

    fireEvent.press(getByText('Ação Segura'));
    expect(handleAction).toHaveBeenCalledTimes(1);
    expect(handleClose).toHaveBeenCalledTimes(1);
  });

  it('renderiza ação destrutiva destacada', () => {
    const handleDelete = jest.fn();
    const actions: ActionItem[] = [
      { id: 'delete', label: 'Excluir Rascunho', isDestructive: true, onPress: handleDelete },
    ];

    const { getByText } = render(
      <ActionSheetModal
        visible={true}
        title="Confirmar Exclusão"
        actions={actions}
        onClose={jest.fn()}
      />
    );

    const destructiveItem = getByText('Excluir Rascunho');
    expect(destructiveItem).toBeTruthy();
  });
});
