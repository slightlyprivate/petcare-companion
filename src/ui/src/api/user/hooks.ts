import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch user notification preferences.
 */
export const useNotificationPreferences = () =>
  useQuery({ queryKey: qk.user.prefs, queryFn: client.getNotificationPreferences });

/**
 * Hook to update user notification preferences.
 * On success, invalidates the user prefs query to refetch updated data.
 */
export function useUpdateNotificationPreferences() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.updateNotificationPreferences,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.user.prefs }),
  });
}

/**
 * Hook to disable all notifications.
 */
export function useDisableAllNotifications() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.disableAllNotifications,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.user.prefs }),
  });
}

/**
 * Hook to enable all notifications.
 */
export function useEnableAllNotifications() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.enableAllNotifications,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.user.prefs }),
  });
}

/**
 * Hook to export user data.
 */
export const useExportUserData = () => useMutation({ mutationFn: client.exportUserData });

/**
 * Hook to delete user data.
 */
export const useDeleteUserData = () => useMutation({ mutationFn: client.deleteUserData });
