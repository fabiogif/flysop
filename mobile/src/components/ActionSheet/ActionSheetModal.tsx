import React from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Modal,
  TouchableWithoutFeedback,
  ScrollView,
} from 'react-native';

export interface ActionItem {
  id: string;
  label: string;
  isDestructive?: boolean;
  onPress: () => void;
}

interface ActionSheetModalProps {
  visible: boolean;
  title: string;
  subtitle?: string;
  actions: ActionItem[];
  onClose: () => void;
}

export const ActionSheetModal: React.FC<ActionSheetModalProps> = ({
  visible,
  title,
  subtitle,
  actions,
  onClose,
}) => {
  const safeActions = actions.filter((a) => !a.isDestructive);
  const destructiveActions = actions.filter((a) => a.isDestructive);

  return (
    <Modal
      transparent
      visible={visible}
      animationType="slide"
      onRequestClose={onClose}
    >
      <TouchableWithoutFeedback onPress={onClose}>
        <View style={styles.backdrop}>
          <TouchableWithoutFeedback>
            <View style={styles.sheetContainer}>
              {/* Drag Handle */}
              <View style={styles.handle} />

              {/* Header */}
              <View style={styles.header}>
                <Text style={styles.title}>{title}</Text>
                {subtitle ? <Text style={styles.subtitle}>{subtitle}</Text> : null}
              </View>

              {/* Content / Safe Actions */}
              <ScrollView style={styles.actionsList}>
                {safeActions.map((action) => (
                  <TouchableOpacity
                    key={action.id}
                    style={styles.actionButton}
                    onPress={() => {
                      action.onPress();
                      onClose();
                    }}
                  >
                    <Text style={styles.actionLabel}>{action.label}</Text>
                  </TouchableOpacity>
                ))}

                {/* Destructive Actions (in red and positioned last) */}
                {destructiveActions.map((action) => (
                  <TouchableOpacity
                    key={action.id}
                    style={[styles.actionButton, styles.destructiveButton]}
                    onPress={() => {
                      action.onPress();
                      onClose();
                    }}
                  >
                    <Text style={[styles.actionLabel, styles.destructiveLabel]}>
                      {action.label}
                    </Text>
                  </TouchableOpacity>
                ))}
              </ScrollView>

              {/* Cancel Action */}
              <TouchableOpacity
                style={styles.cancelButton}
                onPress={onClose}
              >
                <Text style={styles.cancelLabel}>Cancelar</Text>
              </TouchableOpacity>
            </View>
          </TouchableWithoutFeedback>
        </View>
      </TouchableWithoutFeedback>
    </Modal>
  );
};

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  sheetContainer: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingTop: 12,
    paddingBottom: 24,
    paddingHorizontal: 20,
    maxHeight: '75%',
  },
  handle: {
    width: 40,
    height: 5,
    backgroundColor: '#D1D5DB',
    borderRadius: 3,
    alignSelf: 'center',
    marginBottom: 12,
  },
  header: {
    marginBottom: 16,
    alignItems: 'center',
  },
  title: {
    fontSize: 17,
    fontWeight: '700',
    color: '#111827',
  },
  subtitle: {
    fontSize: 13,
    color: '#6B7280',
    marginTop: 4,
    textAlign: 'center',
  },
  actionsList: {
    marginBottom: 12,
  },
  actionButton: {
    minHeight: 48,
    justifyContent: 'center',
    alignItems: 'center',
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: '#E5E7EB',
  },
  actionLabel: {
    fontSize: 16,
    color: '#1E3A8A',
    fontWeight: '500',
  },
  destructiveButton: {
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: '#FEE2E2',
    marginTop: 6,
  },
  destructiveLabel: {
    color: '#DC2626',
    fontWeight: '600',
  },
  cancelButton: {
    minHeight: 48,
    backgroundColor: '#F3F4F6',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 8,
  },
  cancelLabel: {
    fontSize: 16,
    color: '#374151',
    fontWeight: '600',
  },
});
