import React, { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function BooksScreen() {
  const { token } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [books, setBooks] = useState<any[]>([]);
  const [selected, setSelected] = useState<any | null>(null);

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const result = await resourcesApi.books(token);
      setBooks(result.books || []);
    } catch (e: any) {
      setError(e?.message || 'Unable to load books');
    } finally {
      setLoading(false);
    }
  }, [token]);

  useFocusEffect(
    useCallback(() => {
      load();
    }, [load])
  );

  const openDetails = async (circulationId: number) => {
    if (!token) return;
    const detail = await resourcesApi.bookDetails(token, circulationId);
    setSelected(detail.book);
  };

  return (
    <ScreenContainer title="My Books" subtitle="Currently issued books">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {books.map(book => (
        <Card key={book.circulation_id}>
          <Text style={styles.title}>{book.title}</Text>
          <Text style={styles.meta}>{book.author}</Text>
          <Text style={styles.meta}>Due: {book.due_date} | {book.days_left} day(s)</Text>
          <Text style={[styles.status, book.days_left < 0 ? styles.overdue : styles.ok]}>
            {book.status}
          </Text>
          <Pressable style={styles.btn} onPress={() => openDetails(book.circulation_id)}>
            <Text style={styles.btnText}>View Details</Text>
          </Pressable>
        </Card>
      ))}

      {selected ? (
        <Card>
          <Text style={styles.section}>Book Details</Text>
          <Text style={styles.meta}>ISBN: {selected.isbn}</Text>
          <Text style={styles.meta}>Publisher: {selected.publisher}</Text>
          <Text style={styles.meta}>Fine: Rs {selected.fine}</Text>
          <Pressable style={styles.close} onPress={() => setSelected(null)}>
            <Text style={styles.closeText}>Close</Text>
          </Pressable>
        </Card>
      ) : null}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  title: { fontFamily: typography.label, color: colors.primary, fontSize: 16 },
  meta: { fontFamily: typography.body, color: colors.muted, marginTop: 2 },
  status: { marginTop: 6, fontFamily: typography.label },
  overdue: { color: colors.danger },
  ok: { color: colors.success },
  btn: { marginTop: 10, backgroundColor: colors.primary, paddingVertical: 8, borderRadius: 6, alignItems: 'center' },
  btnText: { color: colors.white, fontFamily: typography.label },
  section: { fontFamily: typography.label, color: colors.primary, marginBottom: 4 },
  close: { marginTop: 10, alignSelf: 'flex-end' },
  closeText: { color: colors.primary, fontFamily: typography.label },
});
