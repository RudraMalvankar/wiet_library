import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import * as authApi from '../api/auth';
import { colors, typography } from '../theme';

export function ForgotPasswordScreen({ navigation }: any) {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const onSend = async () => {
    setLoading(true);
    setError('');
    setMessage('');
    try {
      const data = await authApi.requestOtp(email.trim());
      setMessage('OTP generated. Continue to verify screen.');
      navigation.navigate('VerifyOtp', {
        resetToken: data.reset_token,
        email: data.email,
      });
    } catch (e: any) {
      setError(e?.message || 'Unable to send OTP');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.page}>
      <View style={styles.card}>
        <Text style={styles.title}>Forgot Password</Text>
        <Text style={styles.sub}>Enter your student email to generate OTP</Text>

        <TextInput
          value={email}
          onChangeText={setEmail}
          autoCapitalize="none"
          placeholder="Email"
          placeholderTextColor="#9ca3af"
          style={styles.input}
        />

        {error ? <Text style={styles.error}>{error}</Text> : null}
        {message ? <Text style={styles.success}>{message}</Text> : null}

        <Pressable style={styles.button} onPress={onSend} disabled={loading}>
          <Text style={styles.buttonText}>{loading ? 'Sending...' : 'Send OTP'}</Text>
        </Pressable>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  page: { flex: 1, backgroundColor: colors.primary, justifyContent: 'center', padding: 20 },
  card: { backgroundColor: colors.white, borderRadius: 12, padding: 16, borderWidth: 2, borderColor: colors.accent },
  title: { fontSize: 24, color: colors.primary, fontFamily: typography.heading, textAlign: 'center' },
  sub: { textAlign: 'center', color: colors.muted, fontFamily: typography.body, marginBottom: 14 },
  input: { borderWidth: 2, borderColor: colors.accent, borderRadius: 8, backgroundColor: '#f3ebdc', padding: 12, marginBottom: 10, fontFamily: typography.body },
  button: { backgroundColor: colors.primary, borderRadius: 8, paddingVertical: 12, alignItems: 'center', marginTop: 6 },
  buttonText: { color: colors.white, fontFamily: typography.label },
  error: { color: colors.danger, fontFamily: typography.body },
  success: { color: colors.success, fontFamily: typography.body },
});
