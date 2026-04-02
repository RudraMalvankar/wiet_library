import React, { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function NotificationsScreen() {
  const { token } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [items, setItems] = useState<any[]>([]);

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const data = await resourcesApi.notifications(token);
      setItems(data.notifications || []);
    } catch (e: any) {
      setError(e?.message || 'Unable to load notifications');
    } finally {
      setLoading(false);
    }
  }, [token]);

  useFocusEffect(
    useCallback(() => {
      load();
    }, [load])
  );

  const markRead = async (id?: number) => {
    if (!token || !id) return;
    await resourcesApi.markNotificationRead(token, id);
    await load();
  };

  return (
    <ScreenContainer title="Notifications" subtitle="Alerts, reminders and updates">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {items.map(item => (
        <Card key={item.id}>
          <Text style={styles.title}>{item.title}</Text>
          <Text style={styles.message}>{item.message}</Text>
          <Text style={styles.meta}>{item.date}</Text>
          {!item.read && item.notification_id ? (
            <Pressable style={styles.btn} onPress={() => markRead(item.notification_id)}>
              <Text style={styles.btnText}>Mark As Read</Text>
            </Pressable>
          ) : null}
        </Card>
      ))}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  title: { color: colors.primary, fontFamily: typography.label, fontSize: 16 },
  message: { color: colors.text, fontFamily: typography.body, marginTop: 4 },
  meta: { color: colors.muted, fontFamily: typography.body, marginTop: 6, fontSize: 12 },
  btn: { marginTop: 10, backgroundColor: colors.primary, borderRadius: 6, paddingVertical: 8, alignItems: 'center' },
  btnText: { color: colors.white, fontFamily: typography.label },
});
