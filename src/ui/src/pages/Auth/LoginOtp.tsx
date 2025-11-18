import { FormEvent, useMemo, useState } from 'react';
import { useLocation, useNavigate, Navigate } from 'react-router-dom';
import Button from '../../components/Button';
import ErrorMessage from '../../components/ErrorMessage';
import { useRequestOtp, useVerifyOtp, useMe } from '../../api/auth/hooks';
import { ensureCsrf } from '../../lib/csrf';
import { useToast } from '../../lib/notifications';
import { PATHS } from '../../routes/paths';
import Spinner from '../../components/Spinner';
import { useQueryClient } from '@tanstack/react-query';

/**
 * Login page allowing users to authenticate via one-time password (OTP).
 */
export default function LoginOtp() {
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [step, setStep] = useState<'request' | 'verify'>('request');
  const requestOtp = useRequestOtp();
  const verifyOtp = useVerifyOtp();
  const navigate = useNavigate();
  const loc = useLocation();
  const toast = useToast();
  const { data: me, isLoading: meLoading } = useMe();
  const qc = useQueryClient();
  const redirectTo = useMemo(() => {
    const fromState = loc?.state?.redirectTo as string | undefined;
    const fromSearch = new URLSearchParams(loc.search).get('redirectTo') || undefined;
    const fromLegacy = loc?.state?.from
      ? `${loc.state.from.pathname || ''}${loc.state.from.search || ''}${loc.state.from.hash || ''}`
      : undefined;
    return fromState || fromSearch || fromLegacy || PATHS.DASHBOARD.ROOT;
  }, [loc]);

  if (meLoading)
    return (
      <div className="flex items-center justify-center p-8" aria-busy>
        <Spinner />
      </div>
    );

  if (me) return <Navigate to={PATHS.DASHBOARD.ROOT} replace />;

  async function onRequest(e: FormEvent) {
    e.preventDefault();
    await ensureCsrf();
    requestOtp.mutate(
      { email },
      {
        onSuccess: () => {
          setStep('verify');
          toast.success('Code sent. Check your email.');
        },
        onError: (err: unknown) => {
          const msg = (err as { message?: string } | undefined)?.message || 'Failed to send code';
          toast.error(msg);
        },
      },
    );
  }

  async function onVerify(e: FormEvent) {
    e.preventDefault();
    await ensureCsrf();
    verifyOtp.mutate(
      { email, code },
      {
        onSuccess: () => {
          toast.success('Signed in successfully');
          // Mark auth status as true and refetch me to update navigation state
          qc.setQueryData(['auth', 'status'], true);
          qc.invalidateQueries({ queryKey: ['auth', 'status'] });
          qc.invalidateQueries({ queryKey: ['auth', 'me'] });
          navigate(redirectTo, { replace: true });
        },
        onError: (err: unknown) => {
          const msg = (err as { message?: string } | undefined)?.message || 'Invalid code';
          toast.error(msg);
        },
      },
    );
  }

  return (
    <div className="max-w-md mx-auto">
      <h1 className="text-xl font-semibold mb-4">Sign in</h1>
      {step === 'request' ? (
        <form onSubmit={onRequest} className="space-y-3">
          <input
            className="border rounded px-3 py-2 w-full"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
          {requestOtp.isError && (
            <ErrorMessage
              message={(requestOtp.error as { message?: string })?.message || 'Error'}
            />
          )}
          <Button disabled={!email || requestOtp.isPending}>Send Code</Button>
        </form>
      ) : (
        <form onSubmit={onVerify} className="space-y-3">
          <input
            className="border rounded px-3 py-2 w-full"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
          <input
            className="border rounded px-3 py-2 w-full"
            placeholder="Code"
            value={code}
            onChange={(e) => setCode(e.target.value)}
          />
          {verifyOtp.isError && (
            <ErrorMessage message={(verifyOtp.error as { message?: string })?.message || 'Error'} />
          )}
          <Button disabled={!email || !code || verifyOtp.isPending}>Verify</Button>
        </form>
      )}
    </div>
  );
}
