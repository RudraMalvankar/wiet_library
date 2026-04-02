import Constants from 'expo-constants';

function getRuntimeHostIp(): string | null {
  const fromExpoConfig = (Constants.expoConfig as any)?.hostUri as string | undefined;
  const fromManifest2 = (Constants as any)?.manifest2?.extra?.expoGo?.debuggerHost as string | undefined;
  const fromManifest = (Constants as any)?.manifest?.debuggerHost as string | undefined;

  const rawHost = fromExpoConfig || fromManifest2 || fromManifest;
  if (!rawHost) {
    return null;
  }

  const hostPart = rawHost.split(':')[0]?.trim();
  if (!hostPart) {
    return null;
  }

  return hostPart;
}

function resolveApiBaseUrl(): string {
  const envBase = (globalThis as any)?.process?.env?.EXPO_PUBLIC_API_BASE_URL as string | undefined;
  if (envBase?.trim()) {
    return envBase.trim();
  }

  const runtimeIp = getRuntimeHostIp();
  if (runtimeIp) {
    return `http://${runtimeIp}/wiet_lib/student/api/mobile`;
  }

  const extraBase = (Constants.expoConfig?.extra as any)?.apiBaseUrl as string | undefined;
  if (extraBase?.trim()) {
    return extraBase.trim();
  }

  return 'http://10.0.2.2/wiet_lib/student/api/mobile';
}

export const API_BASE_URL = resolveApiBaseUrl();

export const API_PATHS = {
  login: '/auth/login.php',
  me: '/auth/me.php',
  logout: '/auth/logout.php',
  forgotPassword: '/auth/forgot-password.php',
  verifyOtp: '/auth/verify-otp.php',

  dashboard: '/resources/dashboard.php',
  books: '/resources/books.php',
  bookDetails: '/resources/book-details.php',
  history: '/resources/history.php',
  search: '/resources/search.php',
  recommendations: '/resources/recommendations.php',
  profile: '/resources/profile.php',
  digitalId: '/resources/digital-id.php',
  notifications: '/resources/notifications.php',
  notificationsRead: '/resources/notifications-read.php',
  events: '/resources/events.php',
  footfall: '/resources/footfall.php',
  eResources: '/resources/e-resources.php',
};
