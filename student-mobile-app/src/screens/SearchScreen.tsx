import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput } from 'react-native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function SearchScreen() {
  const { token } = useAuth();
  const [q, setQ] = useState('');
  const [results, setResults] = useState<any[]>([]);
  const [featured, setFeatured] = useState<any[]>([]);
  const [message, setMessage] = useState('Use search to find books.');

  const runSearch = async () => {
    if (!token) return;
    try {
      const data = await resourcesApi.search(token, q);
      setResults(data.results || []);
      setFeatured(data.featured_books || []);
      setMessage('');
    } catch (e: any) {
      setMessage(e?.message || 'Search failed');
    }
  };

  return (
    <ScreenContainer title="Search Books" subtitle="Catalog search and featured books">
      <Card>
        <TextInput
          value={q}
          onChangeText={setQ}
          placeholder="Search by title, author, ISBN"
          placeholderTextColor="#9ca3af"
          style={styles.input}
        />
        <Pressable style={styles.btn} onPress={runSearch}>
          <Text style={styles.btnText}>Search</Text>
        </Pressable>
      </Card>

      {message ? <Text style={styles.message}>{message}</Text> : null}

      {results.map(item => (
        <Card key={item.CatNo}>
          <Text style={styles.title}>{item.Title}</Text>
          <Text style={styles.meta}>{item.Author1 || 'Unknown'}</Text>
          <Text style={styles.meta}>Available: {item.copies_available ?? 0}/{item.total_copies ?? 0}</Text>
        </Card>
      ))}

      {featured.length > 0 ? <Text style={styles.heading}>Featured</Text> : null}
      {featured.map(item => (
        <Card key={`f-${item.CatNo}`}>
          <Text style={styles.title}>{item.Title}</Text>
          <Text style={styles.meta}>{item.Author1 || 'Unknown'}</Text>
        </Card>
      ))}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  input: {
    borderWidth: 2,
    borderColor: colors.accent,
    borderRadius: 8,
    backgroundColor: '#f3ebdc',
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontFamily: typography.body,
  },
  btn: {
    marginTop: 10,
    backgroundColor: colors.primary,
    borderRadius: 8,
    paddingVertical: 10,
    alignItems: 'center',
  },
  btnText: { color: colors.white, fontFamily: typography.label },
  message: { color: colors.muted, fontFamily: typography.body, marginBottom: 8 },
  heading: { color: colors.primary, fontFamily: typography.label, marginBottom: 8, marginTop: 8 },
  title: { color: colors.primary, fontFamily: typography.label, fontSize: 16 },
  meta: { color: colors.muted, fontFamily: typography.body, marginTop: 2 },
});
