import { isRouteErrorResponse, useNavigate, useRouteError } from 'react-router-dom';
import Button from './Button';

export default function RouteError() {
  const error = useRouteError() as any;
  const navigate = useNavigate();

  const { status, statusText, message } = normalizeError(error);

  return (
    <div className="max-w-xl mx-auto p-6">
      <h1 className="text-xl font-semibold mb-2">Unexpected Application Error</h1>
      <div className="text-sm text-gray-700 mb-4">
        {status ? (
          <span>
            {status} {statusText || ''}
          </span>
        ) : null}
        <div className="mt-1 text-red-700">{message}</div>
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
        <pre className="mt-4 text-xs bg-gray-50 p-2 rounded overflow-auto">
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
