import React, { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function FootfallScreen() {
  const { token } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [data, setData] = useState<any>(null);
  const [purpose, setPurpose] = useState('Library Visit');

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const result = await resourcesApi.footfall(token);
      setData(result);
    } catch (e: any) {
      setError(e?.message || 'Unable to load footfall');
    } finally {
      setLoading(false);
    }
  }, [token]);

  useFocusEffect(
    useCallback(() => {
      load();
    }, [load])
  );

  const handleCheckin = async () => {
    if (!token) return;
    await resourcesApi.checkin(token, purpose);
    await load();
  };

  const handleCheckout = async () => {
    if (!token) return;
    await resourcesApi.checkout(token);
    await load();
  };

  return (
    <ScreenContainer title="Library Check-in" subtitle="Track and manage your visits">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {!loading && data ? (
        <>
          <Card>
            <Text style={styles.heading}>{data.is_checked_in ? 'You are checked in' : 'You are checked out'}</Text>
            {data.active_entry ? <Text style={styles.meta}>Entry: {data.active_entry.EntryTime}</Text> : null}
            {!data.is_checked_in ? (
              <>
                <TextInput value={purpose} onChangeText={setPurpose} style={styles.input} placeholder="Purpose" />
                <Pressable style={styles.primary} onPress={handleCheckin}>
                  <Text style={styles.primaryText}>Check In</Text>
                </Pressable>
              </>
            ) : (
              <Pressable style={styles.danger} onPress={handleCheckout}>
                <Text style={styles.primaryText}>Check Out</Text>
              </Pressable>
            )}
          </Card>

          <Card>
            <Text style={styles.heading}>Recent Visits</Text>
            {(data.recent_visits || []).map((visit: any, idx: number) => (
              <Text key={idx} style={styles.meta}>
                {visit.visit_date} | {visit.entry_time} | {visit.Purpose || visit.purpose}
              </Text>
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
  input: { borderWidth: 1, borderColor: colors.border, borderRadius: 6, padding: 10, marginTop: 8, marginBottom: 8, fontFamily: typography.body },
  primary: { backgroundColor: colors.primary, borderRadius: 7, paddingVertical: 10, alignItems: 'center' },
  danger: { backgroundColor: colors.danger, borderRadius: 7, paddingVertical: 10, alignItems: 'center', marginTop: 8 },
  primaryText: { color: colors.white, fontFamily: typography.label },
});
