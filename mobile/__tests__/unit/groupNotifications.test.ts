import { groupNotifications } from '../../src/utils/groupNotifications';
import { AppNotification } from '../../src/types';

const make = (id: string, occurrenceId: number | undefined, readAt: string | null): AppNotification => ({
  id,
  type: 'NewOccurrenceForIssuingNotification',
  data: { occurrence_id: occurrenceId, message: id },
  read_at: readAt,
  created_at: '2026-10-20T12:00:00Z',
});

describe('groupNotifications', () => {
  it('agrupa pela ocorrência mantendo a ordem e contando não lidas', () => {
    const groups = groupNotifications([
      make('c', 7, null),
      make('b', 9, '2026-10-20T12:00:00Z'),
      make('a', 7, null),
    ]);

    expect(groups.map((g) => g.key)).toEqual(['occurrence-7', 'occurrence-9']);
    expect(groups[0].latest.id).toBe('c');
    expect(groups[0].items).toHaveLength(2);
    expect(groups[0].unreadCount).toBe(2);
    expect(groups[1].unreadCount).toBe(0);
  });

  it('mantém sozinho aviso sem ocorrência', () => {
    expect(groupNotifications([make('x', undefined, null), make('y', undefined, null)])).toHaveLength(2);
  });
});
