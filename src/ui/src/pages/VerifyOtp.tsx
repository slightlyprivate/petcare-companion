import { useLocation } from 'react-router-dom';

/**
 * Email verification page.
 * @returns Email verification component
 */
export default function VerifyOtp() {
  const qs = new URLSearchParams(useLocation().search);
  const email = qs.get('email') ?? '';
  return (
    <div>
      <h1 className="text-xl font-semibold mb-2">Verify your email</h1>
      <p className="text-sm text-brand-fg mb-4">We sent a code to {email || 'your email'}.</p>
      <form className="space-y-3">
        <div className="space-y-1">
          <label className="text-sm">6-digit code</label>
          <input
            className="w-full border rounded px-3 py-2 tracking-widest"
            inputMode="numeric"
            placeholder="••••••"
          />
        </div>
        <button type="button" className="w-full px-4 py-2 rounded bg-brand-accent text-white">
          Verify
        </button>
      </form>
    </div>
  );
}
