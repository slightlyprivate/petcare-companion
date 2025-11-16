import { ReactNode } from 'react';
import Spinner from './Spinner';
import ErrorMessage from './ErrorMessage';

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
  if (loading)
    return (
      <div className="inline-flex items-center text-sm text-gray-600">
        <Spinner />
        <span className="ml-2">Loading…</span>
      </div>
    );
  if (error) return <ErrorMessage message={(error as any)?.message || String(error) || 'Error'} />;
  return <>{children}</>;
}
