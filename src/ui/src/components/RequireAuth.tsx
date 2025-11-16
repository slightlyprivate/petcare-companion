import { Navigate, useLocation } from 'react-router-dom';
import { useMe } from '../api/auth/hooks';

/**
 * Component that ensures its children are only rendered for authenticated users.
 */
export default function RequireAuth({ children }: { children: JSX.Element }) {
  const { data: me, isLoading } = useMe();
  const loc = useLocation();
  if (isLoading) return <div>Loading…</div>;
  if (!me) return <Navigate to="/login" replace state={{ from: loc }} />;
  return children;
}
