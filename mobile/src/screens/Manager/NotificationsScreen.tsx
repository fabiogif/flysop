import React, { useState, useEffect, useCallback, useMemo } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import { Swipeable } from 'react-native-gesture-handler';
import { apiClient } from '../../api/client';
import { AppNotification } from '../../types';
import { groupNotifications, NotificationGroup } from '../../utils/groupNotifications';
import { timeAgo } from '../../utils/timeAgo';

interface NotificationsScreenProps {
  onOpenOccurrence: (occurrenceId: number) => void;
  onOpenSettings: () => void;
  onUnreadCountChange: (count: number) => void;
}

type FeedResponse = { data: AppNotification[]; meta: { unread_count: number } };

/**
 * Feed in-app do gestor: mais recentes no topo, não lidas destacadas, marcar todas,
 * agrupamento por ocorrência, arrastar para remover, toque abre a ocorrência,
 * estado vazio e atalho para as preferências.
 */
export const NotificationsScreen: React.FC<NotificationsScreenProps> = ({
  onOpenOccurrence,
  onOpenSettings,
  onUnreadCountChange,
}) => {
  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [expanded, setExpanded] = useState<Set<string>>(new Set());
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);

  const groups = useMemo(() => groupNotifications(notifications), [notifications]);
  const unreadCount = notifications.filter((n) => !n.read_at).length;

  useEffect(() => {
    onUnreadCountChange(unreadCount);
  }, [unreadCount, onUnreadCountChange]);

  const fetchFeed = useCallback(async () => {
    try {
      const response = await apiClient.get<FeedResponse>('/manager/notifications');
      setNotifications(response.data.data);
    } catch (error) {
      console.warn('Notifications: falha ao carregar', error);
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchFeed();
  }, [fetchFeed]);

  const markRead = (ids: string[]) => {
    const now = new Date().toISOString();
    setNotifications((current) => current.map((n) => (ids.includes(n.id) && !n.read_at ? { ...n, read_at: now } : n)));
    ids.forEach((id) => apiClient.patch(`/manager/notifications/${id}/read`).catch(() => {}));
  };

  const handleMarkAll = async () => {
    const now = new Date().toISOString();
    setNotifications((current) => current.map((n) => (n.read_at ? n : { ...n, read_at: now })));
    try {
      await apiClient.post('/manager/notifications/read-all');
    } catch {
      fetchFeed();
    }
  };

  const handleDismiss = (group: NotificationGroup) => {
    const ids = group.items.map((n) => n.id);
    setNotifications((current) => current.filter((n) => !ids.includes(n.id)));
    ids.forEach((id) => apiClient.delete(`/manager/notifications/${id}`).catch(() => {}));
  };

  const handleOpen = (group: NotificationGroup) => {
    markRead(group.items.filter((n) => !n.read_at).map((n) => n.id));
    if (group.latest.data.occurrence_id) onOpenOccurrence(group.latest.data.occurrence_id);
  };

  const toggleExpanded = (key: string) => {
    setExpanded((current) => {
      const next = new Set(current);
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  };

  const renderDeleteAction = () => (
    <View style={styles.deleteAction}>
      <Text style={styles.deleteActionText}>Remover</Text>
    </View>
  );

  const renderGroup = ({ item: group }: { item: NotificationGroup }) => {
    const { latest } = group;
    const isUnread = group.unreadCount > 0;
    const isExpanded = expanded.has(group.key);
    const extra = group.items.length - 1;

    return (
      <Swipeable
        renderRightActions={renderDeleteAction}
        onSwipeableOpen={() => handleDismiss(group)}
        overshootRight={false}
      >
        <TouchableOpacity
          style={[styles.item, isUnread && styles.itemUnread]}
          onPress={() => handleOpen(group)}
          accessibilityLabel={`${isUnread ? 'Não lida. ' : ''}${latest.data.message ?? ''}`}
          accessibilityHint="Abre a ocorrência. Arraste para a esquerda para remover."
        >
          <View style={styles.dotColumn}>{isUnread ? <View style={styles.unreadDot} /> : null}</View>
          <View style={styles.itemBody}>
            <View style={styles.itemTopRow}>
              <Text style={styles.protocol}>{latest.data.protocol}</Text>
              <Text style={styles.time}>{timeAgo(latest.created_at)}</Text>
            </View>
            <Text style={[styles.title, isUnread && styles.titleUnread]} numberOfLines={2}>
              {latest.data.type ? `${latest.data.type} — ` : ''}
              {latest.data.title ?? latest.data.message}
            </Text>
            {/* Autor/local só existem no aviso de nova ocorrência; SLA/status não têm */}
            {'author' in latest.data && (
              <Text style={styles.meta} numberOfLines={1}>
                {latest.data.author ? `por ${latest.data.author}` : 'Cidadão anônimo'}
                {latest.data.address ? ` · ${latest.data.address}` : ''}
              </Text>
            )}

            {extra > 0 && (
              <TouchableOpacity onPress={() => toggleExpanded(group.key)} style={styles.groupToggle}>
                <Text style={styles.groupToggleText}>
                  {isExpanded ? 'Ocultar' : `+${extra} ${extra === 1 ? 'aviso' : 'avisos'} desta ocorrência`}
                </Text>
              </TouchableOpacity>
            )}
            {isExpanded &&
              group.items.slice(1).map((n) => (
                <Text key={n.id} style={styles.groupedLine}>
                  {timeAgo(n.created_at)} · {n.data.message}
                </Text>
              ))}
          </View>
        </TouchableOpacity>
      </Swipeable>
    );
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <View style={styles.headerRow}>
          <Text style={styles.heading}>Notificações</Text>
          <TouchableOpacity
            onPress={onOpenSettings}
            style={styles.settingsBtn}
            accessibilityLabel="Preferências de notificação"
          >
            <Text style={styles.settingsBtnText}>⚙️</Text>
          </TouchableOpacity>
        </View>
        <View style={styles.headerRow}>
          <Text style={styles.subheading}>
            {unreadCount > 0 ? `${unreadCount} não ${unreadCount === 1 ? 'lida' : 'lidas'}` : 'Tudo em dia'}
          </Text>
          <TouchableOpacity onPress={handleMarkAll} disabled={unreadCount === 0} style={styles.markAllBtn}>
            <Text style={[styles.markAllText, unreadCount === 0 && styles.markAllTextDisabled]}>
              Marcar todas como lidas
            </Text>
          </TouchableOpacity>
        </View>
      </View>

      {isLoading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color="#1E3A8A" />
        </View>
      ) : (
        <FlatList
          data={groups}
          keyExtractor={(group) => group.key}
          renderItem={renderGroup}
          refreshControl={
            <RefreshControl
              refreshing={isRefreshing}
              onRefresh={() => {
                setIsRefreshing(true);
                fetchFeed();
              }}
            />
          }
          contentContainerStyle={groups.length === 0 && styles.emptyList}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyIcon}>🔔</Text>
              <Text style={styles.emptyTitle}>Nenhuma notificação… ainda</Text>
              <Text style={styles.emptyText}>
                Quando um cidadão registrar uma ocorrência para o seu órgão, você será avisado aqui.
              </Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 8,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  headerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  heading: {
    fontSize: 22,
    fontWeight: '800',
    color: '#111827',
  },
  subheading: {
    fontSize: 13,
    color: '#6B7280',
  },
  settingsBtn: {
    minWidth: 44,
    minHeight: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  settingsBtnText: {
    fontSize: 20,
  },
  markAllBtn: {
    minHeight: 44,
    justifyContent: 'center',
  },
  markAllText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1E3A8A',
  },
  markAllTextDisabled: {
    color: '#9CA3AF',
  },
  item: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    paddingVertical: 14,
    paddingRight: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  itemUnread: {
    backgroundColor: '#EFF6FF',
  },
  dotColumn: {
    width: 28,
    alignItems: 'center',
    paddingTop: 6,
  },
  unreadDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#1E3A8A',
  },
  itemBody: {
    flex: 1,
  },
  itemTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 2,
  },
  protocol: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1E3A8A',
  },
  time: {
    fontSize: 12,
    color: '#6B7280',
  },
  title: {
    fontSize: 15,
    color: '#374151',
  },
  titleUnread: {
    fontWeight: '700',
    color: '#111827',
  },
  meta: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 4,
  },
  groupToggle: {
    marginTop: 6,
    minHeight: 32,
    justifyContent: 'center',
  },
  groupToggleText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1E3A8A',
  },
  groupedLine: {
    fontSize: 12,
    color: '#4B5563',
    marginTop: 4,
  },
  deleteAction: {
    backgroundColor: '#DC2626',
    justifyContent: 'center',
    alignItems: 'flex-end',
    paddingHorizontal: 20,
    flex: 1,
  },
  deleteActionText: {
    color: '#FFFFFF',
    fontWeight: '700',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyList: {
    flexGrow: 1,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 32,
    paddingVertical: 60,
  },
  emptyIcon: {
    fontSize: 54,
    marginBottom: 14,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
    marginBottom: 6,
  },
  emptyText: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    lineHeight: 20,
  },
});
