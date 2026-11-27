import React, { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Modal,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { apiClient } from '../../api/client';

interface StateItem { uf: string; name: string }
interface CityItem { id: number; name: string }

interface CityPickerModalProps {
  visible: boolean;
  initialUf?: string;
  onSelect: (uf: string, city: string) => void;
  onClose: () => void;
}

const normalize = (s: string) =>
  s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

// Estados/municípios do IBGE (GET /states, /states/{uf}/cities) para corrigir o que o geocoder trouxe
export const CityPickerModal: React.FC<CityPickerModalProps> = ({ visible, initialUf, onSelect, onClose }) => {
  const [states, setStates] = useState<StateItem[]>([]);
  const [uf, setUf] = useState(initialUf || '');
  const [cities, setCities] = useState<CityItem[]>([]);
  const [search, setSearch] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!visible) return;
    setUf(initialUf || '');
    setSearch('');
    if (states.length) return;
    apiClient
      .get<{ data: StateItem[] }>('/states')
      .then((res) => setStates(res.data.data))
      .catch(() => setError('Não foi possível carregar os estados.'));
  }, [visible]);

  useEffect(() => {
    if (!uf) {
      setCities([]);
      return;
    }
    setIsLoading(true);
    setError(null);
    apiClient
      .get<{ data: CityItem[] }>(`/states/${uf}/cities`)
      .then((res) => setCities(res.data.data))
      .catch(() => setError('Não foi possível carregar os municípios.'))
      .finally(() => setIsLoading(false));
  }, [uf]);

  const filtered = useMemo(() => {
    const term = normalize(search);
    return term ? cities.filter((c) => normalize(c.name).includes(term)) : cities;
  }, [cities, search]);

  return (
    <Modal visible={visible} animationType="slide" onRequestClose={onClose}>
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <Text style={styles.title}>Estado e município</Text>
          <TouchableOpacity onPress={onClose} accessibilityRole="button" accessibilityLabel="Fechar">
            <Text style={styles.close}>Fechar</Text>
          </TouchableOpacity>
        </View>

        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.ufRow} contentContainerStyle={styles.ufRowContent}>
          {states.map((s) => (
            <TouchableOpacity
              key={s.uf}
              style={[styles.ufChip, uf === s.uf && styles.ufChipActive]}
              onPress={() => setUf(s.uf)}
              accessibilityRole="button"
              accessibilityLabel={s.name}
            >
              <Text style={[styles.ufText, uf === s.uf && styles.ufTextActive]}>{s.uf}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <TextInput
          style={styles.search}
          placeholder={uf ? 'Buscar município...' : 'Escolha a UF acima'}
          value={search}
          onChangeText={setSearch}
          editable={!!uf}
        />

        {error && <Text style={styles.error}>{error}</Text>}
        {isLoading ? (
          <ActivityIndicator style={styles.loading} color="#1E3A8A" />
        ) : (
          <FlatList
            data={filtered}
            keyExtractor={(c) => String(c.id)}
            keyboardShouldPersistTaps="handled"
            initialNumToRender={30}
            renderItem={({ item }) => (
              <TouchableOpacity style={styles.cityItem} onPress={() => onSelect(uf, item.name)}>
                <Text style={styles.cityText}>{item.name}</Text>
              </TouchableOpacity>
            )}
          />
        )}
      </SafeAreaView>
    </Modal>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFFFFF' },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 16 },
  title: { fontSize: 18, fontWeight: '800', color: '#111827' },
  close: { fontSize: 15, fontWeight: '700', color: '#1E3A8A' },
  ufRow: { flexGrow: 0 },
  ufRowContent: { paddingHorizontal: 12, gap: 6 },
  ufChip: { minWidth: 44, height: 44, borderRadius: 22, borderWidth: 1, borderColor: '#D1D5DB', justifyContent: 'center', alignItems: 'center', paddingHorizontal: 8 },
  ufChipActive: { backgroundColor: '#1E3A8A', borderColor: '#1E3A8A' },
  ufText: { fontWeight: '700', color: '#374151' },
  ufTextActive: { color: '#FFFFFF' },
  search: { margin: 16, borderWidth: 1, borderColor: '#D1D5DB', borderRadius: 8, padding: 12, fontSize: 14, color: '#111827' },
  error: { color: '#DC2626', marginHorizontal: 16, marginBottom: 8 },
  loading: { marginTop: 24 },
  cityItem: { paddingVertical: 14, paddingHorizontal: 16, borderBottomWidth: 1, borderBottomColor: '#F3F4F6' },
  cityText: { fontSize: 15, color: '#1F2937' },
});
