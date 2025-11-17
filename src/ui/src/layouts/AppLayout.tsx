import { Link, NavLink, Outlet } from 'react-router-dom';
import { useMe, useLogout } from '../api/auth/hooks';
import Spinner from '../components/Spinner';
import { NAV_ITEMS } from './nav';

// Unified application layout (navigation chrome + routed content)
export default function AppLayout() {
  const { data: me } = useMe();
  const logout = useLogout();

  return (
    <div className="min-h-screen bg-brand-bg text-brand-primary">
      <nav className="flex items-center justify-between px-4 py-3 border-b bg-white">
        <div className="flex items-center space-x-4">
          <Link
            to="/"
            className="font-semibold focus:outline-none focus:ring-2 focus:ring-brand-accent rounded text-brand-primary"
          >
            PetCare
          </Link>
          {NAV_ITEMS.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) =>
                `text-sm ${isActive ? 'text-brand-primary font-medium' : 'text-brand-fg'} focus:outline-none focus:ring-2 focus:ring-brand-accent rounded`
              }
              end={item.end}
            >
              {item.label}
            </NavLink>
          ))}
        </div>
        <div>
          {me ? (
            <button
              className="inline-flex items-center gap-2 text-sm text-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent rounded disabled:opacity-50"
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
              className="text-sm text-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent rounded"
            >
              Login
            </Link>
          )}
        </div>
      </nav>
      <main className="p-4">
        <Outlet />
      </main>
    </div>
  );
}
