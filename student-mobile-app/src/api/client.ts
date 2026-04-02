import { API_BASE_URL } from '../config';

export type ApiResponse<T> = {
  success: boolean;
  data: T;
  message?: string;
};

async function parseJson<T>(response: Response): Promise<ApiResponse<T>> {
  const text = await response.text();
  let payload: any = {};
  try {
    payload = text ? JSON.parse(text) : {};
  } catch {
    throw new Error('Invalid server response');
  }

  if (!response.ok || payload.success === false) {
    throw new Error(payload.message || 'Request failed');
  }

  return payload as ApiResponse<T>;
}

export async function apiGet<T>(path: string, token?: string) {
  const url = `${API_BASE_URL}${path}`;
  let response: Response;
  try {
    response = await fetch(url, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    });
  } catch (error: any) {
    throw new Error(`Network request failed. API URL: ${url}. Check phone/emulator base URL and Apache status.`);
  }

  return parseJson<T>(response);
}

export async function apiPost<T>(path: string, body: Record<string, unknown>, token?: string) {
  const url = `${API_BASE_URL}${path}`;
  let response: Response;
  try {
    response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: JSON.stringify(body),
    });
  } catch (error: any) {
    throw new Error(`Network request failed. API URL: ${url}. Check phone/emulator base URL and Apache status.`);
  }

  return parseJson<T>(response);
}
