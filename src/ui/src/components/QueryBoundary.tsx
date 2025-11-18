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
  error: unknown;
  children: ReactNode;
}) {
  if (loading)
    return (
      <div className="inline-flex items-center text-sm text-brand-fg">
        <Spinner />
        <span className="ml-2">Loading…</span>
      </div>
    );
  if (error)
    return (
      <ErrorMessage
        message={(error as { message?: string } | undefined)?.message || String(error) || 'Error'}
      />
    );
  return <>{children}</>;
}
