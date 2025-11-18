import { FormEvent, useState } from 'react';
import QueryBoundary from '../../../components/QueryBoundary';
import ErrorMessage from '../../../components/ErrorMessage';
import Button from '../../../components/Button';
import { useMe, useLogout, useRequestOtp, useVerifyOtp } from '../../../api/auth/hooks';

/**
 * Section component for authentication-related actions.
 */
export default function AuthSection() {
  const me = useMe();
  const logout = useLogout();
  const requestOtp = useRequestOtp();
  const verifyOtp = useVerifyOtp();
  const [email, setEmail] = useState<string>('');
  const [code, setCode] = useState<string>('');

  function onRequestOtp(e: FormEvent) {
    e.preventDefault();
    if (email) requestOtp.mutate({ email });
  }
  function onVerifyOtp(e: FormEvent) {
    e.preventDefault();
    if (email && code) verifyOtp.mutate({ email, code });
  }

  return (
    <section>
      <h2 className="text-lg font-medium mb-2">Auth</h2>
      <div className="border rounded p-4 space-y-3">
        <div className="flex items-center justify-between">
          <div className="text-sm">Me:</div>
          <QueryBoundary loading={me.isLoading} error={me.error}>
            <pre className="text-xs bg-gray-50 p-2 rounded max-w-full overflow-auto">
              {JSON.stringify(me.data, null, 2)}
            </pre>
          </QueryBoundary>
        </div>
        <div className="grid gap-3 sm:grid-cols-2">
          <form onSubmit={onRequestOtp} className="space-y-2">
            <div className="text-sm font-medium">Request OTP</div>
            <input
              className="border rounded px-3 py-1.5 w-full"
              placeholder="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            {requestOtp.isError && (
              <ErrorMessage
                message={(requestOtp.error as { message?: string })?.message || 'Error'}
              />
            )}
            <Button isLoading={requestOtp.isPending} disabled={!email} size="sm">
              Request
            </Button>
          </form>
          <form onSubmit={onVerifyOtp} className="space-y-2">
            <div className="text-sm font-medium">Verify OTP</div>
            <input
              className="border rounded px-3 py-1.5 w-full"
              placeholder="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            <input
              className="border rounded px-3 py-1.5 w-full"
              placeholder="code"
              value={code}
              onChange={(e) => setCode(e.target.value)}
            />
            {verifyOtp.isError && (
              <ErrorMessage
                message={(verifyOtp.error as { message?: string })?.message || 'Error'}
              />
            )}
            <Button isLoading={verifyOtp.isPending} disabled={!email || !code} size="sm">
              Verify
            </Button>
          </form>
        </div>
        <div>
          <Button
            variant="secondary"
            size="sm"
            onClick={() => logout.mutate()}
            isLoading={logout.isPending}
          >
            Logout
          </Button>
        </div>
      </div>
    </section>
  );
}
