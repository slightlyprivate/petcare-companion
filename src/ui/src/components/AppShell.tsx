import { Link, NavLink } from 'react-router-dom';
import { useMe, useLogout } from '../api/auth/hooks';
import Spinner from './Spinner';

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
          <Link
            to="/"
            className="font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
          >
            PetCare
          </Link>
          <NavLink
            to="/"
            className={({ isActive }) =>
              `text-sm ${isActive ? 'text-indigo-600 font-medium' : 'text-gray-700'} focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded`
            }
          >
            Home
          </NavLink>
          <NavLink
            to="/dashboard"
            className={({ isActive }) =>
              `text-sm ${isActive ? 'text-indigo-600 font-medium' : 'text-gray-700'} focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded`
            }
          >
            Dashboard
          </NavLink>
          <NavLink
            to="/purchases"
            className={({ isActive }) =>
              `text-sm ${isActive ? 'text-indigo-600 font-medium' : 'text-gray-700'} focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded`
            }
          >
            Purchases
          </NavLink>
        </div>
        <div>
          {me ? (
            <button
              className="inline-flex items-center gap-2 text-sm text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded disabled:opacity-50"
              onClick={() => {
                if (!logout.isPending && window.confirm('Are you sure you want to logout?')) {
                  logout.mutate();
                }
              }}
              disabled={logout.isPending}
            >
              {logout.isPending ? <Spinner /> : null}
              <span>Logout</span>
            </button>
          ) : (
            <Link
              to="/login"
              className="text-sm text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
            >
              Login
            </Link>
          )}
        </div>
      </nav>
      <main className="p-4">{children}</main>
    </div>
  );
}
