import { Outlet } from 'react-router-dom';
import DashboardNavigation from '../components/DashboardNavigation';

/**
 * Dashboard layout component
 * @returns JSX.Element
 */
export default function DashboardLayout() {
  return (
    <div className="min-h-screen bg-brand-bg text-brand-primary">
      <nav className="flex items-center justify-between px-4 py-3 border-b bg-white">
        <DashboardNavigation />
      </nav>
      <main className="p-4">
        <Outlet />
      </main>
    </div>
  );
}
