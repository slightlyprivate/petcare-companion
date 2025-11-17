import { Link } from 'react-router-dom';

/**
 * NotFound component
 * @returns JSX.Element
 */
export default function NotFound() {
  return (
    <div className="max-w-xl mx-auto p-6">
      <h1 className="text-xl font-semibold mb-2">Page Not Found</h1>
      <p className="text-sm text-gray-700 mb-4">
        The page you're looking for doesn't exist or was moved.
      </p>
      <Link className="text-blue-600 underline" to="/">
        Go to Home
      </Link>
    </div>
  );
}
