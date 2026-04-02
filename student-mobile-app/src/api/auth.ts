import { API_PATHS } from '../config';
import { apiGet, apiPost } from './client';

type LoginResponse = {
  token: string;
  expires_at: string;
  student: {
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
};

export async function login(email: string, password: string) {
  const response = await apiPost<LoginResponse>(API_PATHS.login, { email, password });
  return response.data;
}

export async function getMe(token: string) {
  const response = await apiGet<{ student: LoginResponse['student'] }>(API_PATHS.me, token);
  return response.data;
}

export async function logout(token: string) {
  const response = await apiPost<{ message: string }>(API_PATHS.logout, {}, token);
  return response.data;
}

export async function requestOtp(email: string) {
  const response = await apiPost<{ reset_token: string; otp: string; email: string }>(
    API_PATHS.forgotPassword,
    { email }
  );
  return response.data;
}

export async function verifyOtp(resetToken: string, email: string, otp: string) {
  const response = await apiPost<{ reset_id: number; member_no: number }>(API_PATHS.verifyOtp, {
    action: 'verify_otp',
    reset_token: resetToken,
    email,
    otp,
  });
  return response.data;
}

export async function resetPassword(resetId: number, memberNo: number, newPassword: string) {
  const response = await apiPost<{ reset: boolean }>(API_PATHS.verifyOtp, {
    action: 'reset_password',
    reset_id: resetId,
    member_no: memberNo,
    new_password: newPassword,
  });
  return response.data;
}
