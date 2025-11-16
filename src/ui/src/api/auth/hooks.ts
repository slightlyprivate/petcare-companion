import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { qk } from '../queryKeys';
import * as client from './client';

/**
 * Hook to fetch the currently authenticated user's information.
 */
export function useMe() {
  return useQuery({ queryKey: qk.auth.me, queryFn: client.getMe, retry: 0 });
}

/**
 * Hook to request a one-time password (OTP) to be sent to the user's email.
 */
export function useRequestOtp() {
  return useMutation({ mutationFn: client.requestOtp });
}

/**
 * Hook to verify the one-time password (OTP) for authentication.
 */
export function useVerifyOtp() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.verifyOtp,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.auth.me }),
  });
}

/**
 * Hook to log out the currently authenticated user.
 */
export function useLogout() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: client.logout,
    onSuccess: () => qc.invalidateQueries({ queryKey: qk.auth.me }),
  });
}
