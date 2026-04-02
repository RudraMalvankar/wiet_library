import React, { useCallback, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function RecommendationsScreen() {
  const { token } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [items, setItems] = useState<any[]>([]);

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const data = await resourcesApi.recommendations(token);
      setItems(data.recommendations || []);
    } catch (e: any) {
      setError(e?.message || 'Unable to load recommendations');
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
    <ScreenContainer title="Recommendations" subtitle="Personalized book suggestions">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {items.map(item => (
        <Card key={item.cat_no}>
          <Text style={styles.title}>{item.title}</Text>
          <Text style={styles.meta}>{item.author}</Text>
          <Text style={styles.meta}>Reason: {item.reason}</Text>
          <Text style={styles.meta}>Available: {item.copies_available}/{item.total_copies}</Text>
        </Card>
      ))}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  title: { color: colors.primary, fontFamily: typography.label, fontSize: 16 },
  meta: { color: colors.text, fontFamily: typography.body, marginTop: 2 },
});
