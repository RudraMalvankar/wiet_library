import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import * as authApi from '../api/auth';
import { colors, typography } from '../theme';

export function VerifyOtpScreen({ route, navigation }: any) {
  const [resetToken, setResetToken] = useState(route?.params?.resetToken || '');
  const [email, setEmail] = useState(route?.params?.email || '');
  const [otp, setOtp] = useState('');
  const [resetId, setResetId] = useState<number | null>(null);
  const [memberNo, setMemberNo] = useState<number | null>(null);
  const [newPassword, setNewPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  const verify = async () => {
    setLoading(true);
    setError('');
    try {
      const data = await authApi.verifyOtp(resetToken, email, otp);
      setResetId(data.reset_id);
      setMemberNo(data.member_no);
      setMessage('OTP verified. Set your new password.');
    } catch (e: any) {
      setError(e?.message || 'OTP verification failed');
    } finally {
      setLoading(false);
    }
  };

  const submitReset = async () => {
    if (!resetId || !memberNo) return;
    setLoading(true);
    setError('');
    try {
      await authApi.resetPassword(resetId, memberNo, newPassword);
      setMessage('Password updated. Please login.');
      setTimeout(() => navigation.navigate('Login'), 600);
    } catch (e: any) {
      setError(e?.message || 'Reset failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.page}>
      <View style={styles.card}>
        <Text style={styles.title}>Verify OTP</Text>

        {!resetId ? (
          <>
            <TextInput value={email} onChangeText={setEmail} placeholder="Email" style={styles.input} />
            <TextInput value={resetToken} onChangeText={setResetToken} placeholder="Reset Token" style={styles.input} />
            <TextInput value={otp} onChangeText={setOtp} keyboardType="number-pad" placeholder="6-digit OTP" style={styles.input} />
            <Pressable style={styles.button} onPress={verify} disabled={loading}>
              <Text style={styles.buttonText}>{loading ? 'Verifying...' : 'Verify OTP'}</Text>
            </Pressable>
          </>
        ) : (
          <>
            <TextInput value={newPassword} onChangeText={setNewPassword} secureTextEntry placeholder="New Password" style={styles.input} />
            <Pressable style={styles.button} onPress={submitReset} disabled={loading}>
              <Text style={styles.buttonText}>{loading ? 'Updating...' : 'Update Password'}</Text>
            </Pressable>
          </>
        )}

        {error ? <Text style={styles.error}>{error}</Text> : null}
        {message ? <Text style={styles.success}>{message}</Text> : null}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  page: { flex: 1, backgroundColor: colors.primary, justifyContent: 'center', padding: 20 },
  card: { backgroundColor: colors.white, borderRadius: 12, padding: 16, borderWidth: 2, borderColor: colors.accent },
  title: { fontSize: 24, color: colors.primary, fontFamily: typography.heading, textAlign: 'center', marginBottom: 12 },
  input: { borderWidth: 2, borderColor: colors.accent, borderRadius: 8, backgroundColor: '#f3ebdc', padding: 12, marginBottom: 10, fontFamily: typography.body },
  button: { backgroundColor: colors.primary, borderRadius: 8, paddingVertical: 12, alignItems: 'center' },
  buttonText: { color: colors.white, fontFamily: typography.label },
  error: { color: colors.danger, fontFamily: typography.body, marginTop: 8 },
  success: { color: colors.success, fontFamily: typography.body, marginTop: 8 },
});
