import { useQueryClient } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch user notification preferences.
 */
export const useNotificationPreferences = () =>
  useAppQuery({ queryKey: qk.user.prefs, queryFn: client.getNotificationPreferences });

/**
 * Hook to update user notification preferences.
 * On success, invalidates the user prefs query to refetch updated data.
 */
export function useUpdateNotificationPreferences() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.updateNotificationPreferences,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.user.prefs }),
  });
}

/**
 * Hook to disable all notifications.
 */
export function useDisableAllNotifications() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.disableAllNotifications,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.user.prefs }),
  });
}

/**
 * Hook to enable all notifications.
 */
export function useEnableAllNotifications() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.enableAllNotifications,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.user.prefs }),
  });
}

/**
 * Hook to export user data.
 */
export const useExportUserData = () => useAppMutation({ mutationFn: client.exportUserData });

/**
 * Hook to delete user data.
 */
export const useDeleteUserData = () => useAppMutation({ mutationFn: client.deleteUserData });
