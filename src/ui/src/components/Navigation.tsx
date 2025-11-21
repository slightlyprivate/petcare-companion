import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { Menu, X, LogOut, LogIn } from 'lucide-react';
import { NAV_ITEMS } from '../layouts/nav';
import { PATHS } from '../routes/paths';

type NavigationProps = {
  isAuthenticated: boolean;
  onLogout?: () => void;
  isLoggingOut?: boolean;
};

/**
 * Mobile navigation menu component
 * @returns JSX.Element
 */
export default function Navigation({ isAuthenticated, onLogout, isLoggingOut }: NavigationProps) {
  const [open, setOpen] = useState(false);
  const panelRef = useRef<HTMLDivElement | null>(null);
  const btnRef = useRef<HTMLButtonElement | null>(null);

  // Removed route-change effect that set state directly; rely on link clicks/logout actions to close.

  useEffect(() => {
    function onDocClick(e: MouseEvent) {
      if (!open) return;
      const t = e.target as Node | null;
      if (
        panelRef.current &&
        !panelRef.current.contains(t) &&
        btnRef.current &&
        !btnRef.current.contains(t)
      ) {
        setOpen(false);
      }
    }
    function onKey(e: KeyboardEvent) {
      if (e.key === 'Escape') setOpen(false);
    }
    document.addEventListener('mousedown', onDocClick);
    document.addEventListener('keydown', onKey);
    return () => {
      document.removeEventListener('mousedown', onDocClick);
      document.removeEventListener('keydown', onKey);
    };
  }, [open]);

  return (
    <div className="relative">
      <button
        ref={btnRef}
        type="button"
        aria-haspopup="menu"
        aria-expanded={open}
        aria-controls="app-navigation-menu"
        onClick={() => setOpen((v) => !v)}
        className="inline-flex items-center justify-center w-10 h-10 rounded focus:outline-none focus:ring-2 focus:ring-brand-accent text-brand-primary"
      >
        {open ? <X className="w-5 h-5" aria-hidden /> : <Menu className="w-5 h-5" aria-hidden />}
        <span className="sr-only">Open navigation menu</span>
      </button>

      {/* Overlay */}
      {open ? (
        <div className="fixed inset-0 z-40" aria-hidden onClick={() => setOpen(false)} />
      ) : null}

      {/* Popover panel */}
      <div
        ref={panelRef}
        id="app-navigation-menu"
        role="menu"
        aria-label="Application navigation"
        className={`absolute right-0 mt-2 w-56 rounded-lg border bg-white shadow-lg z-50 origin-top-right transition-all duration-150 ${
          open ? 'opacity-100 scale-100' : 'pointer-events-none opacity-0 scale-95'
        }`}
      >
        <div className="py-2">
          {NAV_ITEMS.map((item) => (
            <Link
              key={item.to}
              to={item.to}
              className="block px-3 py-2 text-sm text-brand-primary hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
              onClick={() => setOpen(false)}
            >
              {item.label}
            </Link>
          ))}
          <div className="my-2 border-t" />
          {isAuthenticated ? (
            <button
              type="button"
              className="w-full flex items-center gap-2 px-3 py-2 text-left text-sm text-brand-primary hover:bg-gray-50 focus:bg-gray-50 focus:outline-none disabled:opacity-50"
              onClick={() => {
                if (onLogout) onLogout();
                setOpen(false);
              }}
              disabled={!!isLoggingOut}
            >
              <LogOut className="w-4 h-4" aria-hidden /> Logout
            </button>
          ) : (
            <Link
              to={PATHS.AUTH.SIGNIN}
              className="block px-3 py-2 text-sm text-brand-accent hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
              onClick={() => setOpen(false)}
            >
              <span className="inline-flex items-center gap-2">
                <LogIn className="w-4 h-4" aria-hidden /> Sign In
              </span>
            </Link>
          )}
        </div>
      </div>
    </div>
  );
}
