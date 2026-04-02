import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { clearToken, getToken, saveToken } from '../utils/storage';
import * as authApi from '../api/auth';

type Student = {
  student_id: number;
  member_no: number;
  member_code: string;
  name: string;
  email: string;
  branch: string;
  course: string;
  prn: string;
  mobile: string;
  books_issued: number;
};

type AuthContextType = {
  loading: boolean;
  token: string | null;
  student: Student | null;
  signIn: (email: string, password: string) => Promise<void>;
  signOut: () => Promise<void>;
  refreshMe: () => Promise<void>;
};

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [loading, setLoading] = useState(true);
  const [token, setToken] = useState<string | null>(null);
  const [student, setStudent] = useState<Student | null>(null);

  useEffect(() => {
    const init = async () => {
      try {
        const saved = await getToken();
        if (!saved) {
          setLoading(false);
          return;
        }

        const me = await authApi.getMe(saved);
        setToken(saved);
        setStudent(me.student);
      } catch {
        await clearToken();
      } finally {
        setLoading(false);
      }
    };

    init();
  }, []);

  const signIn = async (email: string, password: string) => {
    const result = await authApi.login(email, password);
    await saveToken(result.token);
    setToken(result.token);
    setStudent(result.student);
  };

  const signOut = async () => {
    if (token) {
      try {
        await authApi.logout(token);
      } catch {
      }
    }

    await clearToken();
    setToken(null);
    setStudent(null);
  };

  const refreshMe = async () => {
    if (!token) return;
    const me = await authApi.getMe(token);
    setStudent(me.student);
  };

  const value = useMemo(
    () => ({
      loading,
      token,
      student,
      signIn,
      signOut,
      refreshMe,
    }),
    [loading, token, student]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used inside AuthProvider');
  }
  return context;
}
