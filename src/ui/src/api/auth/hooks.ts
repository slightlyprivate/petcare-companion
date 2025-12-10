import { useQueryClient } from '@tanstack/react-query';
import { useAppQuery, useAppMutation } from '../../lib/appQuery';
import { resetOnLogout } from '../../lib/queryUtils';
import { getAuthToken } from '../../lib/tokenStore';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to check authentication status.
 * Only queries the server if a token exists.
 */
export function useAuthStatus() {
  const hasToken = !!getAuthToken();
  return useAppQuery({
    queryKey: ['auth', 'status'] as const,
    queryFn: client.getStatus,
    retry: 0,
    // Only check status if we have a token; otherwise we know we're not authenticated
    enabled: hasToken,
    // If no token, return false immediately
    placeholderData: hasToken ? undefined : false,
  });
}

export function useMe() {
  const status = useAuthStatus();
  return useAppQuery({
    queryKey: qk.auth.me,
    queryFn: client.getMe,
    retry: 0,
    enabled: !!status.data,
  });
}

/**
 * Hook to request a one-time password (OTP) to be sent to the user's email.
 */
export function useRequestOtp() {
  return useAppMutation({ mutationFn: client.requestOtp });
}

/**
 * Hook to verify the one-time password (OTP) for authentication.
 */
export function useVerifyOtp() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.verifyOtp,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.auth.me }),
  });
}

/**
 * Hook to log out the currently authenticated user.
 */
export function useLogout() {
  const qc = useQueryClient();
  return useAppMutation({
    mutationFn: client.logout,
    onSuccess: () => {
      resetOnLogout(qc);
    },
  });
}
