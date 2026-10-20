// Geocoder do Android devolve o nome do estado ("Bahia"); a API espera a UF (max:2)
const UF_BY_STATE: Record<string, string> = {
  acre: 'AC', alagoas: 'AL', amapa: 'AP', amazonas: 'AM', bahia: 'BA', ceara: 'CE',
  'distrito federal': 'DF', 'espirito santo': 'ES', goias: 'GO', maranhao: 'MA',
  'mato grosso': 'MT', 'mato grosso do sul': 'MS', 'minas gerais': 'MG', para: 'PA',
  paraiba: 'PB', parana: 'PR', pernambuco: 'PE', piaui: 'PI', 'rio de janeiro': 'RJ',
  'rio grande do norte': 'RN', 'rio grande do sul': 'RS', rondonia: 'RO', roraima: 'RR',
  'santa catarina': 'SC', 'sao paulo': 'SP', sergipe: 'SE', tocantins: 'TO',
};

/** Converte o nome do estado em UF; '' quando não reconhece (campo é opcional na API). */
export const toUf = (region?: string | null): string => {
  if (!region) return '';
  if (/^[A-Za-z]{2}$/.test(region)) return region.toUpperCase();
  const key = region
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .replace(/^(state of|estado d[eoa])\s+/i, '')
    .trim()
    .toLowerCase();
  return UF_BY_STATE[key] ?? '';
};
