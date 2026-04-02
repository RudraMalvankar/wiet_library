import { API_PATHS } from '../config';
import { apiGet, apiPost } from './client';

export const resourcesApi = {
  dashboard: (token: string) => apiGet<any>(API_PATHS.dashboard, token).then(r => r.data),
  books: (token: string) => apiGet<any>(API_PATHS.books, token).then(r => r.data),
  bookDetails: (token: string, circulationId: number) =>
    apiGet<any>(`${API_PATHS.bookDetails}?circulation_id=${circulationId}`, token).then(r => r.data),
  history: (token: string) => apiGet<any>(API_PATHS.history, token).then(r => r.data),
  search: (token: string, q = '') =>
    apiGet<any>(`${API_PATHS.search}?q=${encodeURIComponent(q)}`, token).then(r => r.data),
  recommendations: (token: string) => apiGet<any>(API_PATHS.recommendations, token).then(r => r.data),
  profile: (token: string) => apiGet<any>(API_PATHS.profile, token).then(r => r.data),
  digitalId: (token: string) => apiGet<any>(API_PATHS.digitalId, token).then(r => r.data),
  notifications: (token: string) => apiGet<any>(API_PATHS.notifications, token).then(r => r.data),
  markNotificationRead: (token: string, notificationId: number) =>
    apiPost<any>(API_PATHS.notificationsRead, { notification_id: notificationId }, token).then(r => r.data),
  events: (token: string) => apiGet<any>(API_PATHS.events, token).then(r => r.data),
  footfall: (token: string) => apiGet<any>(API_PATHS.footfall, token).then(r => r.data),
  checkin: (token: string, purpose: string) =>
    apiPost<any>(API_PATHS.footfall, { action: 'checkin', purpose }, token).then(r => r.data),
  checkout: (token: string) => apiPost<any>(API_PATHS.footfall, { action: 'checkout' }, token).then(r => r.data),
  eResources: (token: string) => apiGet<any>(API_PATHS.eResources, token).then(r => r.data),
};
