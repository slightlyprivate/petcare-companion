import { Navigate } from 'react-router-dom';
import { useMe } from '../api/auth/hooks';
import Spinner from './Spinner';
import { PATHS } from '../routes/paths';

type RequireRoleProps = {
  children: JSX.Element;
  allow: string | string[];
  fallbackTo?: string;
};

/**
 * Guard component that ensures the current user has one of the allowed roles.
 */
export default function RequireRole({ children, allow, fallbackTo }: RequireRoleProps) {
  const { data: me, isLoading } = useMe();
  const allowed = Array.isArray(allow) ? allow : [allow];

  if (isLoading)
    return (
      <div className="flex items-center justify-center p-8" aria-busy>
        <Spinner />
      </div>
    );

  // If missing user or role not permitted, bounce
  if (!me || (me as any).role == null || !allowed.includes((me as any).role)) {
    return <Navigate to={fallbackTo || PATHS.DASHBOARD.ROOT} replace />;
  }

  return children;
}
