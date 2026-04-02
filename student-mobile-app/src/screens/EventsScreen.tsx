import React, { useCallback, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function EventsScreen() {
  const { token } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [data, setData] = useState<any>({ active: [], upcoming: [], completed: [] });

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const result = await resourcesApi.events(token);
      setData(result);
    } catch (e: any) {
      setError(e?.message || 'Unable to load events');
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
    <ScreenContainer title="Library Events" subtitle="Active, upcoming and completed events">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {(['active', 'upcoming', 'completed'] as const).map(section => (
        <Card key={section}>
          <Text style={styles.heading}>{section.toUpperCase()}</Text>
          {data[section]?.length === 0 ? <Text style={styles.meta}>No events</Text> : null}
          {data[section]?.map((item: any) => (
            <Text key={item.event_id} style={styles.meta}>
              {item.title} | {item.start_date} | {item.venue}
            </Text>
          ))}
        </Card>
      ))}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  heading: { color: colors.primary, fontFamily: typography.label, marginBottom: 6, fontSize: 14 },
  meta: { color: colors.text, fontFamily: typography.body, marginBottom: 3 },
});
