import { Navigate } from 'react-router-dom';
import { useAuthStatus, useMe } from '../api/auth/hooks';
import Spinner from './Spinner';
import { PATHS } from '../routes/paths';
import type { Role } from '../constants/roles';

type RequireRoleProps = {
  children: JSX.Element;
  allow: string | string[];
  fallbackTo?: string;
};

/**
 * Guard component that ensures the current user has one of the allowed roles.
 */
export default function RequireRole({ children, allow, fallbackTo }: RequireRoleProps) {
  const { data: isAuthenticated, isLoading: statusLoading } = useAuthStatus();
  const { data: me, isLoading: meLoading } = useMe();
  const allowed = Array.isArray(allow) ? allow : [allow];

  if (statusLoading || meLoading)
    return (
      <div className="flex items-center justify-center p-8" aria-busy>
        <Spinner />
      </div>
    );

  if (!isAuthenticated) {
    return <Navigate to={PATHS.AUTH.SIGNIN} replace />;
  }

  // If missing user or role not permitted, bounce
  const role = (me as { role?: Role } | null | undefined)?.role;
  if (!me || role == null || !allowed.includes(role)) {
    return <Navigate to={fallbackTo || PATHS.DASHBOARD.ROOT} replace />;
  }

  return children;
}
