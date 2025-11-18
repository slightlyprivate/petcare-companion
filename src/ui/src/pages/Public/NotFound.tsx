import { Link } from 'react-router-dom';
import { PATHS } from '../../routes/paths';

/**
 * NotFound component
 * @returns JSX.Element
 */
export default function NotFound() {
  return (
    <div className="max-w-xl mx-auto p-6 bg-brand-bg rounded">
      <h1 className="text-xl font-semibold mb-2 text-brand-primary">Page Not Found</h1>
      <p className="text-sm text-brand-fg mb-4">
        The page you're looking for doesn't exist or was moved.
      </p>
      <Link className="text-brand-accent underline" to={PATHS.ROOT}>
        Go to Home
      </Link>
    </div>
  );
}
