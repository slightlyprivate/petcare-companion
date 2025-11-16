import { FormEvent, useState } from 'react';
import Button from '../components/Button';
import ErrorMessage from '../components/ErrorMessage';
import { useRequestOtp, useVerifyOtp } from '../api/auth/hooks';
import { ensureCsrf } from '../lib/csrf';

/**
 * Login page allowing users to authenticate via one-time password (OTP).
 */
export default function LoginOtp() {
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [step, setStep] = useState<'request' | 'verify'>('request');
  const requestOtp = useRequestOtp();
  const verifyOtp = useVerifyOtp();

  async function onRequest(e: FormEvent) {
    e.preventDefault();
    await ensureCsrf();
    requestOtp.mutate({ email }, { onSuccess: () => setStep('verify') });
  }

  async function onVerify(e: FormEvent) {
    e.preventDefault();
    await ensureCsrf();
    verifyOtp.mutate({ email, code });
  }

  return (
    <div className="max-w-md mx-auto">
      <h1 className="text-xl font-semibold mb-4">Login via OTP</h1>
      {step === 'request' ? (
        <form onSubmit={onRequest} className="space-y-3">
          <input
            className="border rounded px-3 py-2 w-full"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
          {requestOtp.isError && (
            <ErrorMessage message={(requestOtp.error as any)?.message || 'Error'} />
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
            <ErrorMessage message={(verifyOtp.error as any)?.message || 'Error'} />
          )}
          <Button disabled={!email || !code || verifyOtp.isPending}>Verify</Button>
        </form>
      )}
    </div>
  );
}
