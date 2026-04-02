import React, { useCallback, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import QRCode from 'react-native-qrcode-svg';
import { ScreenContainer } from '../components/ScreenContainer';
import { Card, ErrorState, LoadingState } from '../components/Ui';
import { useAuth } from '../context/AuthContext';
import { resourcesApi } from '../api/resources';
import { colors, typography } from '../theme';

export function DigitalIdScreen() {
  const { token } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [card, setCard] = useState<any>(null);

  const load = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    setError('');
    try {
      const data = await resourcesApi.digitalId(token);
      setCard(data.card);
    } catch (e: any) {
      setError(e?.message || 'Unable to load digital ID');
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
    <ScreenContainer title="Digital ID" subtitle="Your library identity card">
      {loading ? <LoadingState /> : null}
      {error ? <ErrorState message={error} /> : null}

      {!loading && card ? (
        <View style={styles.cardWrap}>
          <Text style={styles.college}>WALCHAND INSTITUTE OF TECHNOLOGY</Text>
          <Text style={styles.name}>{card.name}</Text>
          <Text style={styles.meta}>Member: {card.member_code}</Text>
          <Text style={styles.meta}>PRN: {card.student_id}</Text>
          <Text style={styles.meta}>Branch: {card.department}</Text>
          <Text style={styles.meta}>Valid Till: {card.expiry_date || 'N/A'}</Text>
          <Card>
            <Text style={styles.label}>Student QR</Text>
            <View style={styles.qrWrap}>
              <QRCode
                value={String(card.qr_code || card.member_code || card.student_id)}
                size={180}
                color={colors.text}
                backgroundColor={colors.white}
              />
            </View>
            <Text style={styles.code}>{String(card.qr_code || card.member_code || card.student_id)}</Text>
            <Text style={styles.label}>Barcode</Text>
            <Text style={styles.code}>{card.barcode}</Text>
          </Card>
        </View>
      ) : null}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  cardWrap: {
    backgroundColor: colors.primary,
    borderRadius: 14,
    padding: 16,
    borderWidth: 2,
    borderColor: colors.accent,
  },
  college: { color: colors.accent, fontFamily: typography.label, fontSize: 12 },
  name: { color: colors.white, fontFamily: typography.heading, fontSize: 22, marginTop: 6 },
  meta: { color: '#d6def1', fontFamily: typography.body, marginTop: 3 },
  label: { color: colors.primary, fontFamily: typography.label, marginTop: 4 },
  code: { color: colors.text, fontFamily: typography.bodyBold },
  qrWrap: {
    marginTop: 8,
    marginBottom: 8,
    alignItems: 'center',
  },
});
