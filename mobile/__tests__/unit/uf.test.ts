import { toUf } from '../../src/utils/uf';

describe('toUf', () => {
  it('converte nome do estado (com/sem acento e prefixo) em UF', () => {
    expect(toUf('Bahia')).toBe('BA');
    expect(toUf('São Paulo')).toBe('SP');
    expect(toUf('State of Rio de Janeiro')).toBe('RJ');
    expect(toUf('Estado do Pará')).toBe('PA');
  });

  it('mantém UF já abreviada e ignora valores desconhecidos', () => {
    expect(toUf('ba')).toBe('BA');
    expect(toUf('California')).toBe('');
    expect(toUf(undefined)).toBe('');
  });
});
