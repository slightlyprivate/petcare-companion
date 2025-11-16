import { Link } from 'react-router-dom';
import { useMe, useLogout } from '../api/auth/hooks';

/**
 * App shell component that includes navigation and layout.
 */
export default function AppShell({ children }: { children?: React.ReactNode }) {
  const { data: me } = useMe();
  const logout = useLogout();

  return (
    <div className="min-h-screen">
      <nav className="flex items-center justify-between px-4 py-3 border-b bg-white">
        <div className="flex items-center space-x-4">
          <Link to="/" className="font-semibold">
            PetCare
          </Link>
          <Link to="/" className="text-sm text-gray-700">
            Home
          </Link>
          <Link to="/purchases" className="text-sm text-gray-700">
            Purchases
          </Link>
        </div>
        <div>
          {me ? (
            <button className="text-sm text-indigo-600" onClick={() => logout.mutate()}>
              Logout
            </button>
          ) : (
            <Link to="/login" className="text-sm text-indigo-600">
              Login
            </Link>
          )}
        </div>
      </nav>
      <main className="p-4">
        {children}
      </main>
    </div>
  );
}
