import { NavLink } from 'react-router-dom';
import { PATHS } from '../routes/paths';

/**
 * Dashboard navigation component
 * @returns JSX.Element
 */
export default function DashboardNavigation() {
  const linkClass = ({ isActive }: { isActive: boolean }) =>
    isActive ? 'font-medium' : 'text-brand-fg';

  return (
    <div className="flex items-center gap-4">
      <NavLink to={PATHS.DASHBOARD.PETS} className={linkClass}>
        My Pets
      </NavLink>
      <NavLink to={PATHS.DASHBOARD.APPOINTMENTS} className={linkClass}>
        Appointments
      </NavLink>
      <NavLink to={PATHS.DASHBOARD.GIFTS} className={linkClass}>
        Gifts
      </NavLink>
      <NavLink to={PATHS.DASHBOARD.ACCOUNT} className={linkClass}>
        Account
      </NavLink>
    </div>
  );
}
