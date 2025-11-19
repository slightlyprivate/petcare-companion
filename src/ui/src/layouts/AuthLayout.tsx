import { Outlet, Link } from 'react-router-dom';
import { PATHS } from '../routes/paths';

/**
 * Auth layout for authentication-related pages.
 * @returns Auth layout component
 */
export default function AuthLayout() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-start p-6">
      <div className="w-full max-w-md space-y-6">
        <header className="text-center">
          <img
            src="/brand/illustrations/hero.png"
            alt="PetCare Companion Hero"
            className="w-50 max-w-md mx-auto mb-6 rounded-lg"
          />
          <Link to={PATHS.ROOT} className="text-lg font-semibold text-brand-primary">
            PetCare
          </Link>
          <div className="text-sm text-brand-fg">Sign in to continue</div>
        </header>
        <div className="border rounded-lg bg-white p-6 shadow-sm">
          <Outlet />
        </div>
      </div>
    </div>
  );
}
