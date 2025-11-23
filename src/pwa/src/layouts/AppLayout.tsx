import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useMe, useLogout } from '../api/auth/hooks';
import { PATHS } from '../routes/paths';
import Navigation from '../components/Navigation';

// Unified application layout (navigation chrome + routed content)
export default function AppLayout() {
  const { data: me } = useMe();
  const logout = useLogout();
  const navigate = useNavigate();

  return (
    <div className="min-h-screen bg-brand-bg text-brand-primary">
      <header className="sticky top-0 z-50 border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/75">
        <div className="mx-auto flex items-center justify-between px-4 py-3">
          <Link
            to={PATHS.ROOT}
            className="font-semibold focus:outline-none focus:ring-2 focus:ring-brand-accent rounded text-brand-primary"
          >
            PetCare
          </Link>
          <Navigation
            isAuthenticated={!!me}
            onLogout={() => {
              if (!logout.isPending)
                logout.mutate(undefined, {
                  onSuccess: () => {
                    navigate(PATHS.ROOT, { replace: true });
                  },
                });
            }}
            isLoggingOut={logout.isPending}
          />
        </div>
      </header>
      <main className="p-4">
        <Outlet />
      </main>
    </div>
  );
}
