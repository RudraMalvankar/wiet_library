import React, { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState, StatCard } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function DashboardScreen({ navigation }: any) {
  const { token, student } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [data, setData] = useState<any>(null);

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const result = await resourcesApi.dashboard(token);
      setData(result);
    } catch (e: any) {
      setError(e?.message || 'Unable to load dashboard');
    } finally {
      setLoading(false);
    }
  }, [token]);

  useFocusEffect(
    useCallback(() => {
      load();
    }, [load])
  );

  const navigateTo = (routeName: string) => {
    try {
      navigation.navigate(routeName);
    } catch {
      navigation.getParent?.()?.navigate(routeName);
    }
  };

  return (
    <ScreenContainer title="Dashboard" subtitle={`Welcome, ${student?.name ?? 'Student'}`}>
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {!loading && data ? (
        <>
          <View style={styles.statsGrid}>
            <StatCard label="Books Issued" value={data.quick_stats.books_issued} tone="info" />
            <StatCard label="Due Soon" value={data.quick_stats.books_due} tone="warning" />
          </View>
          <View style={styles.statsGrid}>
            <StatCard label="Pending Fines" value={`Rs ${data.quick_stats.pending_fines}`} tone="danger" />
            <StatCard label="Recommendations" value={data.quick_stats.recommendations} tone="success" />
          </View>

          <Card>
            <Text style={styles.sectionTitle}>Quick Access</Text>
            <View style={styles.quickRow}>
              <QuickButton label="History" onPress={() => navigateTo('History')} />
              <QuickButton label="Recommendations" onPress={() => navigateTo('Recommendations')} />
            </View>
            <View style={styles.quickRow}>
              <QuickButton label="Digital ID" onPress={() => navigateTo('DigitalId')} />
              <QuickButton label="Events" onPress={() => navigateTo('Events')} />
            </View>
            <View style={styles.quickRow}>
              <QuickButton label="Footfall" onPress={() => navigateTo('Footfall')} />
              <QuickButton label="E-Resources" onPress={() => navigateTo('EResources')} />
            </View>
          </Card>

          <Card>
            <Text style={styles.sectionTitle}>Upcoming Due Books</Text>
            {data.upcoming_due.length === 0 ? <Text style={styles.muted}>No books due in next 7 days.</Text> : null}
            {data.upcoming_due.map((item: any) => (
              <View key={item.CirculationID} style={styles.rowLine}>
                <Text style={styles.bookTitle}>{item.Title}</Text>
                <Text style={styles.muted}>{item.days_left} day(s) left</Text>
              </View>
            ))}
          </Card>
        </>
      ) : null}
    </ScreenContainer>
  );
}

function QuickButton({ label, onPress }: { label: string; onPress: () => void }) {
  return (
    <Pressable style={styles.quickButton} onPress={onPress}>
      <Text style={styles.quickText}>{label}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  statsGrid: { flexDirection: 'row', gap: 10, marginBottom: 10 },
  sectionTitle: { fontFamily: typography.label, color: colors.primary, marginBottom: 8, fontSize: 15 },
  quickRow: { flexDirection: 'row', gap: 10, marginBottom: 10 },
  quickButton: {
    flex: 1,
    backgroundColor: colors.primary,
    borderRadius: 8,
    paddingVertical: 12,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.accent,
  },
  quickText: { color: colors.white, fontFamily: typography.label, fontSize: 12 },
  muted: { color: colors.muted, fontFamily: typography.body },
  rowLine: { paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: colors.border },
  bookTitle: { color: colors.text, fontFamily: typography.bodyBold },
});
