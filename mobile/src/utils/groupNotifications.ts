import { AppNotification } from '../types';

export interface NotificationGroup {
  key: string;
  latest: AppNotification;
  items: AppNotification[];
  unreadCount: number;
}

/**
 * Agrupa avisos da mesma ocorrência num item só (o mais recente na frente), mantendo a
 * ordem cronológica reversa que a API já entrega. Avisos sem ocorrência ficam sozinhos.
 */
export const groupNotifications = (notifications: AppNotification[]): NotificationGroup[] => {
  const groups = new Map<string, NotificationGroup>();

  for (const notification of notifications) {
    const key = notification.data.occurrence_id
      ? `occurrence-${notification.data.occurrence_id}`
      : notification.id;
    const group = groups.get(key);

    if (group) {
      group.items.push(notification);
      if (!notification.read_at) group.unreadCount += 1;
    } else {
      groups.set(key, {
        key,
        latest: notification,
        items: [notification],
        unreadCount: notification.read_at ? 0 : 1,
      });
    }
  }

  return [...groups.values()];
};
