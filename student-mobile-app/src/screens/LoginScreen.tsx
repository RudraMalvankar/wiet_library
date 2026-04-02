import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { useAuth } from '../context/AuthContext';
import { colors, typography } from '../theme';

export function LoginScreen({ navigation }: any) {
  const { signIn } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const onLogin = async () => {
    setError('');
    setLoading(true);
    try {
      await signIn(email.trim(), password);
    } catch (e: any) {
      setError(e?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.page}>
      <View style={styles.card}>
        <Text style={styles.title}>Student Login</Text>
        <Text style={styles.subtitle}>WIET Library Mobile Portal</Text>

        <TextInput
          value={email}
          onChangeText={setEmail}
          autoCapitalize="none"
          placeholder="Email"
          placeholderTextColor="#9ca3af"
          style={styles.input}
        />
        <TextInput
          value={password}
          onChangeText={setPassword}
          secureTextEntry
          placeholder="Password"
          placeholderTextColor="#9ca3af"
          style={styles.input}
        />

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <Pressable style={styles.button} onPress={onLogin} disabled={loading}>
          <Text style={styles.buttonText}>{loading ? 'Signing In...' : 'Sign In'}</Text>
        </Pressable>

        <Pressable onPress={() => navigation.navigate('ForgotPassword')}>
          <Text style={styles.link}>Forgot password?</Text>
        </Pressable>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  page: {
    flex: 1,
    backgroundColor: colors.primary,
    justifyContent: 'center',
    padding: 20,
  },
  card: {
    backgroundColor: colors.white,
    borderRadius: 14,
    borderWidth: 2,
    borderColor: colors.accent,
    padding: 18,
  },
  title: {
    fontSize: 28,
    color: colors.primary,
    fontFamily: typography.heading,
    textAlign: 'center',
  },
  subtitle: {
    textAlign: 'center',
    color: colors.muted,
    fontFamily: typography.body,
    marginBottom: 16,
  },
  input: {
    borderWidth: 2,
    borderColor: colors.accent,
    borderRadius: 8,
    backgroundColor: '#f3ebdc',
    paddingHorizontal: 12,
    paddingVertical: 11,
    marginBottom: 10,
    color: colors.text,
    fontFamily: typography.body,
  },
  button: {
    backgroundColor: colors.primary,
    borderRadius: 8,
    paddingVertical: 12,
    alignItems: 'center',
    marginTop: 4,
  },
  buttonText: {
    color: colors.white,
    fontFamily: typography.label,
  },
  link: {
    textAlign: 'center',
    marginTop: 14,
    color: colors.primary,
    fontFamily: typography.label,
  },
  error: {
    color: colors.danger,
    marginBottom: 8,
    fontFamily: typography.body,
  },
});
