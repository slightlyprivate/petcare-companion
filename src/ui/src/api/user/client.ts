import { api, proxy } from '../../lib/http';
import type { NotificationPreferences } from '../types';

/**
 * Fetch user profile details.
 */
export const getUserProfile = () => api('/user/profile');

/**
 * Update user profile details.
 */
export const updateUserProfile = (payload: { name?: string; email?: string }) =>
  proxy('/user/profile', { method: 'PUT', body: payload });

/**
 * User notification preferences endpoints
 */
export const getNotificationPreferences = (): Promise<NotificationPreferences> =>
  api('/user/notification-preferences');

/**
 * Update user notification preferences.
 */
export const updateNotificationPreferences = (payload: Partial<NotificationPreferences>) =>
  proxy('/user/notification-preferences', { method: 'PUT', body: payload });

/**
 * Enable or disable all notifications.
 */
export const disableAllNotifications = () =>
  proxy('/user/notification-preferences/disable-all', { method: 'POST' });

/**
 * Enable all notifications.
 */
export const enableAllNotifications = () =>
  proxy('/user/notification-preferences/enable-all', { method: 'POST' });

/**
 * Data export and deletion endpoints
 */
export const exportUserData = () => api('/user/data/export');

/**
 * Delete user data.
 */
export const deleteUserData = () => proxy('/user/data', { method: 'DELETE' });
