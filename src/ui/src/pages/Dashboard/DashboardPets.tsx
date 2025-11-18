import { Link } from 'react-router-dom';
import { PATHS } from '../../routes/paths';

/**
 * Dashboard pets management page.
 * @returns Dashboard pets management component
 */
export default function DashboardPets() {
  return (
    <div>
      <div className="flex items-center justify-between mb-3">
        <h1 className="text-xl font-semibold">My Pets</h1>
        <Link to={PATHS.DASHBOARD.PETS_NEW} className="text-sm text-brand-accent">
          + Add Pet
        </Link>
      </div>
      <div className="text-sm text-brand-fg">Grid of my pets goes here.</div>
    </div>
  );
}
