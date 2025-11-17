import { Outlet, Link } from 'react-router-dom';
import { PATHS } from '../routes/paths';

/**
 * Auth layout for authentication-related pages.
 * @returns Auth layout component
 */
export default function AuthLayout() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-6">
      <div className="w-full max-w-md space-y-6">
        <header className="text-center">
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
