import { api } from '../../lib/http';

/**
 * Fetch user profile details.
 */
export const getUserProfile = () => api('/user/profile');

/**
 * Update user profile details.
 */
export const updateUserProfile = (payload: { name?: string; email?: string }) =>
  api('/user/profile', { method: 'PUT', body: payload });

/**
 * User notification preferences endpoints
 */
export const getNotificationPreferences = () => api('/user/notification-preferences');

/**
 * Update user notification preferences.
 */
export const updateNotificationPreferences = (payload: any) =>
  api('/user/notification-preferences', { method: 'PUT', body: payload });

/**
 * Enable or disable all notifications.
 */
export const disableAllNotifications = () =>
  api('/user/notification-preferences/disable-all', { method: 'POST' });

/**
 * Enable all notifications.
 */
export const enableAllNotifications = () =>
  api('/user/notification-preferences/enable-all', { method: 'POST' });

/**
 * Data export and deletion endpoints
 */
export const exportUserData = () => api('/user/data/export');

/**
 * Delete user data.
 */
export const deleteUserData = () => api('/user/data', { method: 'DELETE' });
