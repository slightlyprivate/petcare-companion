import { useLocation } from 'react-router-dom';
import TextInput from '../../components/TextInput';
import Button from '../../components/Button';

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
        <TextInput label="6-digit code" inputMode="numeric" placeholder="••••••" />
        <Button type="button" className="w-full">
          Verify
        </Button>
      </form>
    </div>
  );
}
