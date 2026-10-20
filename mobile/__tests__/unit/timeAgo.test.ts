import { timeAgo } from '../../src/utils/timeAgo';

describe('timeAgo', () => {
  const now = new Date('2026-10-20T12:00:00Z');

  it('formata intervalos curtos', () => {
    expect(timeAgo('2026-10-20T11:59:30Z', now)).toBe('agora');
    expect(timeAgo('2026-10-20T11:45:00Z', now)).toBe('há 15 min');
    expect(timeAgo('2026-10-20T09:00:00Z', now)).toBe('há 3 h');
  });

  it('usa ontem, dias e depois a data', () => {
    expect(timeAgo('2026-10-19T10:00:00Z', now)).toBe('ontem');
    expect(timeAgo('2026-10-16T12:00:00Z', now)).toBe('há 4 dias');
    expect(timeAgo('2026-10-01T12:00:00Z', now)).toMatch(/^\d{2}\/10\/2026$/);
  });
});
