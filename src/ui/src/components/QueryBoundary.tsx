import { ReactNode } from 'react';

/**
 * Component to handle loading and error states for queries.
 */
export default function QueryBoundary({
  loading,
  error,
  children,
}: {
  loading: boolean;
  error: any;
  children: ReactNode;
}) {
  if (loading) return <div>Loading…</div>;
  if (error) return <div className="text-red-600">Error: {String(error?.message || error)}</div>;
  return <>{children}</>;
}
