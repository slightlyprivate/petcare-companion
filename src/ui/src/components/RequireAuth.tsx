import { Navigate, useLocation } from 'react-router-dom';
import { useAuthStatus } from '../api/auth/hooks';
import Spinner from './Spinner';
import { PATHS } from '../routes/paths';

/**
 * Component that ensures its children are only rendered for authenticated users.
 */
export default function RequireAuth({ children }: { children: JSX.Element }) {
  const { data: isAuthenticated, isLoading } = useAuthStatus();
  const loc = useLocation();
  if (isLoading)
    return (
      <div className="flex items-center justify-center p-8" aria-busy>
        <Spinner />
      </div>
    );
  if (!isAuthenticated) {
    const redirectTo = `${loc.pathname}${loc.search}${loc.hash}`;
    const qs = new URLSearchParams({ redirectTo }).toString();
    return (
      <Navigate
        to={{ pathname: PATHS.AUTH.SIGNIN, search: `?${qs}` }}
        replace
        state={{ from: loc, redirectTo }}
      />
    );
  }
  return children;
}
