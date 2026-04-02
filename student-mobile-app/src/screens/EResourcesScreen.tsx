import React, { useCallback, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function EResourcesScreen() {
  const { token } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [data, setData] = useState<any>(null);

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const result = await resourcesApi.eResources(token);
      setData(result);
    } catch (e: any) {
      setError(e?.message || 'Unable to load e-resources');
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
    <ScreenContainer title="E-Resources" subtitle="Digital resources and databases">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {!loading && data ? (
        <>
          <Card>
            <Text style={styles.heading}>Stats</Text>
            <Text style={styles.meta}>Databases: {data.stats.databases_available}</Text>
            <Text style={styles.meta}>Resources: {data.stats.total_resources}</Text>
            <Text style={styles.meta}>Monthly Downloads: {data.stats.this_month_downloads}</Text>
          </Card>

          <Card>
            <Text style={styles.heading}>Databases</Text>
            {data.databases.map((d: any) => (
              <Text key={d.name} style={styles.meta}>{d.name} ({d.access_type})</Text>
            ))}
          </Card>

          <Card>
            <Text style={styles.heading}>Popular E-books</Text>
            {data.ebooks.map((b: any) => (
              <Text key={b.title} style={styles.meta}>{b.title} - {b.author}</Text>
            ))}
          </Card>
        </>
      ) : null}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  heading: { color: colors.primary, fontFamily: typography.label, marginBottom: 6 },
  meta: { color: colors.text, fontFamily: typography.body, marginBottom: 3 },
});
