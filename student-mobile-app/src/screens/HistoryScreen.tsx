import React, { useCallback, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function HistoryScreen() {
  const { token } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [rows, setRows] = useState<any[]>([]);

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const data = await resourcesApi.history(token);
      setRows(data.history || []);
    } catch (e: any) {
      setError(e?.message || 'Unable to load history');
    } finally {
      setLoading(false);
    }
  }, [token]);

  useFocusEffect(
    useCallback(() => {
      load();
    }, [load])
  );

  return (
    <ScreenContainer title="Borrowing History" subtitle="All issue and return records">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {rows.map(item => (
        <Card key={item.transaction_id}>
          <Text style={styles.title}>{item.title}</Text>
          <Text style={styles.meta}>{item.author}</Text>
          <Text style={styles.meta}>Issued: {item.issue_date}</Text>
          <Text style={styles.meta}>Returned: {item.return_date || '-'}</Text>
          <Text style={styles.status}>{item.status}</Text>
        </Card>
      ))}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  title: { color: colors.primary, fontFamily: typography.label, fontSize: 16 },
  meta: { color: colors.text, fontFamily: typography.body, marginTop: 2 },
  status: { marginTop: 6, color: colors.info, fontFamily: typography.label },
});
