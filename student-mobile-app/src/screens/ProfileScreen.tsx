import React, { useCallback, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function ProfileScreen() {
  const { token, signOut } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [profile, setProfile] = useState<any>(null);

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const data = await resourcesApi.profile(token);
      setProfile(data);
    } catch (e: any) {
      setError(e?.message || 'Unable to load profile');
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
    <ScreenContainer title="My Profile" subtitle="Personal and library information">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {!loading && profile ? (
        <>
          <Card>
            <Text style={styles.heading}>{profile.personal_info.full_name}</Text>
            <Text style={styles.meta}>PRN: {profile.personal_info.student_id}</Text>
            <Text style={styles.meta}>Email: {profile.personal_info.email}</Text>
            <Text style={styles.meta}>Phone: {profile.personal_info.phone}</Text>
          </Card>

          <Card>
            <Text style={styles.heading}>Academic</Text>
            <Text style={styles.meta}>Course: {profile.academic_info.course}</Text>
            <Text style={styles.meta}>Branch: {profile.academic_info.branch}</Text>
            <Text style={styles.meta}>Valid Till: {profile.academic_info.membership_valid_till}</Text>
          </Card>

          <Card>
            <Text style={styles.heading}>Library Stats</Text>
            <Text style={styles.meta}>Books Borrowed: {profile.library_stats.total_books_borrowed}</Text>
            <Text style={styles.meta}>Current Borrowed: {profile.library_stats.current_borrowed}</Text>
            <Text style={styles.meta}>Total Visits: {profile.library_stats.total_visits}</Text>
            <Text style={styles.meta}>Fine Paid: Rs {profile.library_stats.total_fines_paid}</Text>
          </Card>

          <Text onPress={signOut} style={styles.logout}>Logout</Text>
        </>
      ) : null}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  heading: { color: colors.primary, fontFamily: typography.label, fontSize: 17, marginBottom: 6 },
  meta: { color: colors.text, fontFamily: typography.body, marginBottom: 4 },
  logout: { textAlign: 'center', color: colors.danger, fontFamily: typography.label, marginTop: 12 },
});
