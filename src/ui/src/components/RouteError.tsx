import { isRouteErrorResponse, useNavigate, useRouteError } from 'react-router-dom';
import Button from './Button';

export default function RouteError() {
  const error = useRouteError() as any;
  const navigate = useNavigate();

  const { status, statusText, message } = normalizeError(error);
  const isAuth = status === 401 || status === 403;

  return (
    <div className="max-w-xl mx-auto p-6 bg-brand-bg rounded">
      <h1 className="text-xl font-semibold mb-2 text-brand-primary">
        {isAuth ? 'Access Restricted' : 'Unexpected Application Error'}
      </h1>
      <div className="text-sm text-brand-fg mb-4">
        {status ? (
          <span>
            {status} {statusText || ''}
          </span>
        ) : null}
        <div className={isAuth ? 'mt-1 text-brand-fg' : 'mt-1 text-brand-danger'}>
          {isAuth ? 'You do not have permission to view this resource.' : message}
        </div>
      </div>
      <div className="flex gap-2">
        <Button onClick={() => navigate(-1)} size="sm" variant="secondary">
          Go Back
        </Button>
        <Button onClick={() => navigate('/')} size="sm">
          Home
        </Button>
      </div>
      {import.meta.env.DEV && error?.stack ? (
        <pre className="mt-4 text-xs bg-brand-bg p-2 rounded overflow-auto border border-brand-muted">
          {String(error.stack)}
        </pre>
      ) : null}
    </div>
  );
}

function normalizeError(err: any): { status?: number; statusText?: string; message: string } {
  if (!err) return { message: 'Unknown error' };
  if (isRouteErrorResponse(err)) {
    return {
      status: err.status,
      statusText: err.statusText,
      message:
        (err.data && (err.data.message || err.data.error?.message)) || err.statusText || 'Error',
    };
  }
  const status = typeof err.status === 'number' ? err.status : undefined;
  const statusText = err.statusText || undefined;
  const message = err.message || String(err) || 'Error';
  return { status, statusText, message };
}
